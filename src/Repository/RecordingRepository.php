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
use RuntimeException;

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
            ->orderBy('recording.id')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        throw new RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('recording');
        $qb->andWhere('recording.column LIKE :q');
        $qb->orderBy('recording.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchTitleQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('recording');
        $qb->addSelect('MATCH (recording.title) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
