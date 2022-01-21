<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Bookmark;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Bookmark find($id, $lockMode = null, $lockVersion = null)
 * @method Bookmark[] findAll()
 * @method Bookmark[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Bookmark findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Bookmark>
 */
class BookmarkRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Bookmark::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('bookmark')
            ->orderBy('bookmark.title')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('bookmark');
        $qb->andWhere('bookmark.title LIKE :q');
        $qb->orderBy('bookmark.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('bookmark');
        $qb->addSelect('MATCH (bookmark.title) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
