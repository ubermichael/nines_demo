<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Artefact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Artefact find($id, $lockMode = null, $lockVersion = null)
 * @method Artefact[] findAll()
 * @method Artefact[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Artefact findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Artefact>
 */
class ArtefactRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Artefact::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('artefact')
            ->orderBy('artefact.title')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('artefact');
        $qb->andWhere('artefact.title LIKE :q');
        $qb->orderBy('artefact.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('artefact');
        $qb->addSelect('MATCH (artefact.title, artefact.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
