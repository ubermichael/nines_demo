<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method null|Work find($id, $lockMode = null, $lockVersion = null)
 * @method Work[] findAll()
 * @method Work[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Work findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Work>
 */
class WorkRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Work::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('work')
            ->orderBy('work.id')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        throw new RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('work');
        $qb->andWhere('work.column LIKE :q');
        $qb->orderBy('work.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
