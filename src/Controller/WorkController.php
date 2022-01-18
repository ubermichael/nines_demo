<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Work;
use App\Form\WorkType;
use App\Repository\WorkRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work")
 */
class WorkController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="work_index", methods={"GET"})
     */
    public function index(Request $request, WorkRepository $workRepository) : Response {
        $query = $workRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('work/index.html.twig', [
            'works' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="work_search", methods={"GET"})
     */
    public function search(Request $request, WorkRepository $workRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $workRepository->searchQuery($q);
            $works = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $works = [];
        }

        return $this->render('work/search.html.twig', [
            'works' => $works,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="work_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, WorkRepository $workRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($workRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Work $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="work_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $work = new Work();
        $form = $this->createForm(WorkType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($work);
            $em->flush();
            $this->addFlash('success', 'The new work has been saved.');

            return $this->redirectToRoute('work_show', ['id' => $work->getId()]);
        }

        return $this->render('work/new.html.twig', [
            'work' => $work,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="work_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="work_show", methods={"GET"})
     */
    public function show(Work $work) : Response {
        return $this->render('work/show.html.twig', [
            'work' => $work,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="work_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Work $work, EntityManagerInterface $em) : Response {
        $form = $this->createForm(WorkType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated work has been saved.');

            return $this->redirectToRoute('work_show', ['id' => $work->getId()]);
        }

        return $this->render('work/edit.html.twig', [
            'work' => $work,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="work_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Work $work, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $work->getId(), $request->request->get('_token'))) {
            $em->remove($work);
            $em->flush();
            $this->addFlash('success', 'The work has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('work_index');
    }
}
