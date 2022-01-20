<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Title;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method null|Title find($id, $lockMode = null, $lockVersion = null)
 * @method Title[] findAll()
 * @method Title[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Title findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Title>
 */
class TitleRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Title::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('title')
            ->orderBy('title.main')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('title');
        $qb->andWhere('title.main LIKE :q');
        $qb->orderBy('title.main', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('title');
        $qb->addSelect('MATCH (title.main, title.sub, title.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
