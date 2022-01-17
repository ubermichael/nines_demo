<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Element;
use App\Form\ElementType;
use App\Repository\ElementRepository;

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
 * @Route("/element")
 */
class ElementController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="element_index", methods={"GET"})
     */
    public function index(Request $request, ElementRepository $elementRepository) : Response {
        $query = $elementRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('element/index.html.twig', [
            'elements' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="element_search", methods={"GET"})
     */
    public function search(Request $request, ElementRepository $elementRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $elementRepository->searchQuery($q);
            $elements = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $elements = [];
        }

        return $this->render('element/search.html.twig', [
            'elements' => $elements,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="element_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, ElementRepository $elementRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($elementRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Element $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="element_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $element = new Element();
        $form = $this->createForm(ElementType::class, $element);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($element);
            $em->flush();
            $this->addFlash('success', 'The new element has been saved.');

            return $this->redirectToRoute('element_show', ['id' => $element->getId()]);
        }

        return $this->render('element/new.html.twig', [
            'element' => $element,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="element_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request) : Response {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="element_show", methods={"GET"})
     */
    public function show(Element $element) : Response {
        return $this->render('element/show.html.twig', [
            'element' => $element,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="element_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Element $element, EntityManagerInterface $em) : Response {
        $form = $this->createForm(ElementType::class, $element);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated element has been saved.');

            return $this->redirectToRoute('element_show', ['id' => $element->getId()]);
        }

        return $this->render('element/edit.html.twig', [
            'element' => $element,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="element_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Element $element, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $element->getId(), $request->request->get('_token'))) {
            $em->remove($element);
            $em->flush();
            $this->addFlash('success', 'The element has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('element_index');
    }
}
