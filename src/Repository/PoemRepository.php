<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Poem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Nines\DublinCoreBundle\Entity\Value;

/**
 * @method null|Poem find($id, $lockMode = null, $lockVersion = null)
 * @method Poem[] findAll()
 * @method Poem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Poem findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Poem>
 */
class PoemRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Poem::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('poem')
            ->orderBy('poem.id')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $cls = Poem::class;
        $qb = $this->createQueryBuilder('poem');
        $qb->innerJoin(Value::class, 'value', Join::WITH, "value.entity = CONCAT('{$cls}:', poem.id)");
        $qb->addSelect('MATCH(value.data) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->setParameter('q', $q);
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'desc');
        $qb->addOrderBy('poem.id', 'asc');

        return $qb->getQuery();
    }
}
