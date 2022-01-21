<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Artefact;
use App\Form\ArtefactType;
use App\Repository\ArtefactRepository;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\ImageControllerTrait;
use Nines\MediaBundle\Entity\Image;
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
 * @Route("/artefact")
 */
class ArtefactController extends AbstractController implements PaginatorAwareInterface {
    use ImageControllerTrait;
    use PaginatorTrait;

    /**
     * @Route("/", name="artefact_index", methods={"GET"})
     */
    public function index(Request $request, ArtefactRepository $artefactRepository) : Response {
        $query = $artefactRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('artefact/index.html.twig', [
            'artefacts' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="artefact_search", methods={"GET"})
     */
    public function search(Request $request, ArtefactRepository $artefactRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $artefactRepository->searchQuery($q);
            $artefacts = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $artefacts = [];
        }

        return $this->render('artefact/search.html.twig', [
            'artefacts' => $artefacts,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="artefact_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, ArtefactRepository $artefactRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($artefactRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Artefact $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="artefact_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $artefact = new Artefact();
        $form = $this->createForm(ArtefactType::class, $artefact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artefact);
            $em->flush();
            $this->addFlash('success', 'The new artefact has been saved.');

            return $this->redirectToRoute('artefact_show', ['id' => $artefact->getId()]);
        }

        return $this->render('artefact/new.html.twig', [
            'artefact' => $artefact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="artefact_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="artefact_show", methods={"GET"})
     */
    public function show(Artefact $artefact) : Response {
        return $this->render('artefact/show.html.twig', [
            'artefact' => $artefact,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="artefact_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Artefact $artefact, EntityManagerInterface $em) : Response {
        $form = $this->createForm(ArtefactType::class, $artefact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated artefact has been saved.');

            return $this->redirectToRoute('artefact_show', ['id' => $artefact->getId()]);
        }

        return $this->render('artefact/edit.html.twig', [
            'artefact' => $artefact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="artefact_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Artefact $artefact, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $artefact->getId(), $request->request->get('_token'))) {
            $em->remove($artefact);
            $em->flush();
            $this->addFlash('success', 'The artefact has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('artefact_index');
    }

    /**
     * @Route("/{id}/new_image", name="artefact_new_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     */
    public function newImage(Request $request, EntityManagerInterface $em, Artefact $artefact) : Response {
        $context = $this->newImageAction($request, $em, $artefact, 'artefact_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('artefact/new_image.html.twig', array_merge($context, [
            'artefact' => $artefact,
        ]));
    }

    /**
     * @Route("/{id}/edit_image/{image_id}", name="artefact_edit_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("image", options={"id" = "image_id"})
     *
     * @throws Exception
     */
    public function editImage(Request $request, EntityManagerInterface $em, Artefact $artefact, Image $image) : Response {
        $context = $this->editImageAction($request, $em, $artefact, $image, 'artefact_show');
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return $this->render('artefact/edit_image.html.twig', array_merge($context, [
            'artefact' => $artefact,
        ]));
    }

    /**
     * @Route("/{id}/delete_image/{image_id}", name="artefact_delete_image", methods={"DELETE"})
     * @ParamConverter("image", options={"id" = "image_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function deleteImage(Request $request, EntityManagerInterface $em, Artefact $artefact, Image $image) : RedirectResponse {
        return $this->deleteImageAction($request, $em, $artefact, $image, 'artefact_show');
    }
}
