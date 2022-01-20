<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Bookmark;
use App\Form\BookmarkType;
use App\Repository\BookmarkRepository;

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
 * @Route("/bookmark")
 */
class BookmarkController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="bookmark_index", methods={"GET"})
     */
    public function index(Request $request, BookmarkRepository $bookmarkRepository) : Response {
        $query = $bookmarkRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('bookmark/index.html.twig', [
            'bookmarks' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="bookmark_search", methods={"GET"})
     */
    public function search(Request $request, BookmarkRepository $bookmarkRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $bookmarkRepository->searchQuery($q);
            $bookmarks = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $bookmarks = [];
        }

        return $this->render('bookmark/search.html.twig', [
            'bookmarks' => $bookmarks,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="bookmark_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, BookmarkRepository $bookmarkRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($bookmarkRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Bookmark $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="bookmark_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $bookmark = new Bookmark();
        $form = $this->createForm(BookmarkType::class, $bookmark);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($bookmark);
            $em->flush();
            $this->addFlash('success', 'The new bookmark has been saved.');

            return $this->redirectToRoute('bookmark_show', ['id' => $bookmark->getId()]);
        }

        return $this->render('bookmark/new.html.twig', [
            'bookmark' => $bookmark,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="bookmark_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="bookmark_show", methods={"GET"})
     */
    public function show(Bookmark $bookmark) : Response {
        return $this->render('bookmark/show.html.twig', [
            'bookmark' => $bookmark,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="bookmark_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Bookmark $bookmark, EntityManagerInterface $em) : Response {
        $form = $this->createForm(BookmarkType::class, $bookmark);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated bookmark has been saved.');

            return $this->redirectToRoute('bookmark_show', ['id' => $bookmark->getId()]);
        }

        return $this->render('bookmark/edit.html.twig', [
            'bookmark' => $bookmark,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="bookmark_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Bookmark $bookmark, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $bookmark->getId(), $request->request->get('_token'))) {
            $em->remove($bookmark);
            $em->flush();
            $this->addFlash('success', 'The bookmark has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('bookmark_index');
    }
}
