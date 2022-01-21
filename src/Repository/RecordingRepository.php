<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Recording;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Recording find($id, $lockMode = null, $lockVersion = null)
 * @method Recording[] findAll()
 * @method Recording[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Recording findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Recording>
 */
class RecordingRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Recording::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('recording')
            ->orderBy('recording.title')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('recording');
        $qb->andWhere('recording.title LIKE :q');
        $qb->orderBy('recording.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('recording');
        $qb->addSelect('MATCH (recording.title) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
