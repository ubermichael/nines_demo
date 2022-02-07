<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Title;
use App\Form\TitleType;
use App\Index\TitleIndex;
use App\Repository\TitleRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Services\SolrManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/title")
 */
class TitleController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="title_index", methods={"GET"})
     */
    public function index(Request $request, TitleRepository $titleRepository) : Response {
        $query = $titleRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('title/index.html.twig', [
            'titles' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="title_search", methods={"GET"})
     *
     * @throws NotConfiguredException
     */
    public function search(Request $request, TitleIndex $index, SolrManager $solr) : Response {
        $q = $request->query->get('q');
        $result = null;
        if ($q) {
            $filters = $request->query->get('filter', []);
            $rangeFilters = $request->query->get('filter_range', []);

            $order = null;
            $m = [];
            if (preg_match('/^(\\w+).(asc|desc)$/', $request->query->get('order', 'score.desc'), $m)) {
                $order = [$m[1] => $m[2]];
            }

            $query = $index->searchQuery($q, $filters, $rangeFilters, $order);
            $result = $solr->execute($query, $this->paginator, [
                'page' => (int) $request->query->get('page', 1),
                'pageSize' => (int) $this->getParameter('page_size'),
            ]);
        }

        return $this->render('title/search.html.twig', [
            'q' => $q,
            'result' => $result,
        ]);
    }

    /**
     * @Route("/typeahead", name="title_typeahead", methods={"GET"})
     */
    public function typeahead(Request $request, TitleRepository $titleRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($titleRepository->typeaheadQuery($q)->execute() as $result) {
            // @var Title $result
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="title_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $em) : Response {
        $title = new Title();
        $form = $this->createForm(TitleType::class, $title);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($title);
            $em->flush();
            $this->addFlash('success', 'The new title has been saved.');

            return $this->redirectToRoute('title_show', ['id' => $title->getId()]);
        }

        return $this->render('title/new.html.twig', [
            'title' => $title,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new_popup", name="title_new_popup", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function new_popup(Request $request, EntityManagerInterface $em) : Response {
        return $this->new($request, $em);
    }

    /**
     * @Route("/{id}", name="title_show", methods={"GET"})
     */
    public function show(Title $title) : Response {
        return $this->render('title/show.html.twig', [
            'title' => $title,
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="title_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Title $title, EntityManagerInterface $em) : Response {
        $form = $this->createForm(TitleType::class, $title);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The updated title has been saved.');

            return $this->redirectToRoute('title_show', ['id' => $title->getId()]);
        }

        return $this->render('title/edit.html.twig', [
            'title' => $title,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="title_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Title $title, EntityManagerInterface $em) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $title->getId(), $request->request->get('_token'))) {
            $em->remove($title);
            $em->flush();
            $this->addFlash('success', 'The title has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('title_index');
    }
}
