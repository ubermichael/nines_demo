<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;

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
 * @Route("/document")
 */
class DocumentController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="document_index", methods={"GET"})
     */
    public function index(Request $request, DocumentRepository $documentRepository) : Response {
        $query = $documentRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('document/index.html.twig', [
            'documents' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="document_search", methods={"GET"})
     */
    public function search(Request $request, DocumentRepository $documentRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $documentRepository->searchQuery($q);
            $documents = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $documents = [];
        }

        return $this->render('document/search.html.twig', [
            'documents' => $documents,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="document_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, DocumentRepository $documentRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($documentRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Document $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="document_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($document);
            $em->flush();
            $this->addFlash('success', 'The new document has been saved.');

            return $this->redirectToRoute('document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="document_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="document_show", methods={"GET"})
     */
    public function show(Document $document) : Response {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="document_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Document $document, EntityManagerInterface $em) : Response {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated document has been saved.');

            return $this->redirectToRoute('document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="document_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Document $document, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $em->remove($document);
            $em->flush();
            $this->addFlash('success', 'The document has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('document_index');
    }
}
