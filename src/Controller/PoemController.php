<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Poem;
use App\Form\PoemType;
use App\Repository\PoemRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/poem")
 */
class PoemController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="poem_index", methods={"GET"})
     */
    public function index(Request $request, PoemRepository $poemRepository) : Response {
        $query = $poemRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('poem/index.html.twig', [
            'poems' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="poem_search", methods={"GET"})
     */
    public function search(Request $request, PoemRepository $poemRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $poemRepository->searchQuery($q);
            $poems = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $poems = [];
        }

        return $this->render('poem/search.html.twig', [
            'poems' => $poems,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/new", name="poem_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $poem = new Poem();
        $form = $this->createForm(PoemType::class, $poem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($poem);
            $em->flush();
            $this->addFlash('success', 'The new poem has been saved.');

            return $this->redirectToRoute('poem_show', ['id' => $poem->getId()]);
        }

        return $this->render('poem/new.html.twig', [
            'poem' => $poem,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="poem_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="poem_show", methods={"GET"})
     */
    public function show(Poem $poem) : Response {
        return $this->render('poem/show.html.twig', [
            'poem' => $poem,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="poem_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Poem $poem, EntityManagerInterface $em) : Response {
        $form = $this->createForm(PoemType::class, $poem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated poem has been saved.');

            return $this->redirectToRoute('poem_show', ['id' => $poem->getId()]);
        }

        return $this->render('poem/edit.html.twig', [
            'poem' => $poem,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="poem_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Poem $poem, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $poem->getId(), $request->request->get('_token'))) {
            $em->remove($poem);
            $em->flush();
            $this->addFlash('success', 'The poem has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('poem_index');
    }
}
