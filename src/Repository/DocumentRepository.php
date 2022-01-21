<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Document find($id, $lockMode = null, $lockVersion = null)
 * @method Document[] findAll()
 * @method Document[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Document findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Document::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('document')
            ->orderBy('document.title')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('document');
        $qb->andWhere('document.title LIKE :q');
        $qb->orderBy('document.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('document');
        $qb->addSelect('MATCH (document.title, document.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
