<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Value;
use App\Form\ValueType;
use App\Repository\ValueRepository;

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
 * @Route("/value")
 */
class ValueController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="value_index", methods={"GET"})
     */
    public function index(Request $request, ValueRepository $valueRepository) : Response {
        $query = $valueRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('value/index.html.twig', [
            'values' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="value_search", methods={"GET"})
     */
    public function search(Request $request, ValueRepository $valueRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $valueRepository->searchQuery($q);
            $values = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $values = [];
        }

        return $this->render('value/search.html.twig', [
            'values' => $values,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/typeahead", name="value_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, ValueRepository $valueRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($valueRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Value $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="value_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $value = new Value();
        $form = $this->createForm(ValueType::class, $value);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($value);
            $em->flush();
            $this->addFlash('success', 'The new value has been saved.');

            return $this->redirectToRoute('value_show', ['id' => $value->getId()]);
        }

        return $this->render('value/new.html.twig', [
            'value' => $value,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="value_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request) : Response {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="value_show", methods={"GET"})
     */
    public function show(Value $value) : Response {
        return $this->render('value/show.html.twig', [
            'value' => $value,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="value_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Value $value, EntityManagerInterface $em) : Response {
        $form = $this->createForm(ValueType::class, $value);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated value has been saved.');

            return $this->redirectToRoute('value_show', ['id' => $value->getId()]);
        }

        return $this->render('value/edit.html.twig', [
            'value' => $value,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="value_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Value $value, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $value->getId(), $request->request->get('_token'))) {
            $em->remove($value);
            $em->flush();
            $this->addFlash('success', 'The value has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('value_index');
    }
}
