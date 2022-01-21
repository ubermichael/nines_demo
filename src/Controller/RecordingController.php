<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Recording;
use App\Form\RecordingType;
use App\Repository\RecordingRepository;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\AudioControllerTrait;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\AudioManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/recording")
 */
class RecordingController extends AbstractController implements PaginatorAwareInterface {
    use AudioControllerTrait;
    use PaginatorTrait;

    /**
     * @Route("/", name="recording_index", methods={"GET"})
     */
    public function index(Request $request, RecordingRepository $recordingRepository) : Response {
        $query = $recordingRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('recording/index.html.twig', [
            'recordings' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="recording_search", methods={"GET"})
     */
    public function search(Request $request, RecordingRepository $recordingRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $recordingRepository->searchQuery($q);
            $recordings = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $recordings = [];
        }

        return $this->render('recording/search.html.twig', [
            'recordings' => $recordings,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="recording_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, RecordingRepository $recordingRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($recordingRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Recording $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="recording_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $recording = new Recording();
        $form = $this->createForm(RecordingType::class, $recording);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recording);
            $em->flush();
            $this->addFlash('success', 'The new recording has been saved.');

            return $this->redirectToRoute('recording_show', ['id' => $recording->getId()]);
        }

        return $this->render('recording/new.html.twig', [
            'recording' => $recording,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="recording_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="recording_show", methods={"GET"})
     */
    public function show(Recording $recording) : Response {
        return $this->render('recording/show.html.twig', [
            'recording' => $recording,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="recording_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Recording $recording, EntityManagerInterface $em) : Response {
        $form = $this->createForm(RecordingType::class, $recording);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated recording has been saved.');

            return $this->redirectToRoute('recording_show', ['id' => $recording->getId()]);
        }

        return $this->render('recording/edit.html.twig', [
            'recording' => $recording,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="recording_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Recording $recording, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $recording->getId(), $request->request->get('_token'))) {
            $em->remove($recording);
            $em->flush();
            $this->addFlash('success', 'The recording has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('recording_index');
    }

    /**
     * @Route("/{id}/new_audio", name="recording_new_audio", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     */
    public function newAudio(Request $request, EntityManagerInterface $em, Recording $recording) : Response {
        $context = $this->newAudioAction($request, $em, $recording, 'recording_show');

        return $this->render('recording/new_audio.html.twig', array_merge($context, [
            'recording' => $recording,
        ]));
    }

    /**
     * @Route("/{id}/edit_audio/{audio_id}", name="recording_edit_audio", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id" = "audio_id"})
     *
     * @throws Exception
     */
    public function editAudio(Request $request, EntityManagerInterface $em, Recording $recording, Audio $audio, AudioManager $fileUploader) : Response {
        $context = $this->editAudioAction($request, $em, $recording, $audio, 'recording_show');

        return $this->render('recording/edit_audio.html.twig', array_merge($context, [
            'recording' => $recording,
        ]));
    }

    /**
     * @Route("/{id}/delete_audio/{audio_id}", name="recording_delete_audio", methods={"DELETE"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id" = "audio_id"})
     *
     * @throws Exception
     */
    public function deleteAudio(Request $request, EntityManagerInterface $em, Recording $recording, Audio $audio) : RedirectResponse {
        return $this->deleteAudioAction($request, $em, $recording, $audio, 'recording_index');
    }
}
