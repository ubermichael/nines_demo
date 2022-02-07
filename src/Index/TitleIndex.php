<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Index;

use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Index\AbstractIndex;
use Solarium\QueryType\Select\Query\Query;

class TitleIndex extends AbstractIndex {
    /**
     * @param ?array<string,array<string>> $filters
     * @param ?array<string,array<string>> $rangeFilters
     * @param ?array<string,string> $order
     *
     * @throws NotConfiguredException
     */
    public function searchQuery(string $q, ?array $filters = [], ?array $rangeFilters = [], ?array $order = []) : Query {
        $qb = $this->createQueryBuilder();
        $qb->setQueryString($q);
        $qb->setDefaultField('content');

        $qb->addFilter('type', ['Title']);

        foreach ($filters as $key => $values) {
            $qb->addFilter($key, $values);
        }

        foreach ($rangeFilters as $key => $values) {
            foreach ($values as $v) {
                list($start, $end) = explode(' ', $v);
                $qb->addFilterRange($key, $start, $end);
            }
        }

        $qb->addFacetRange('price', 0, 10, 1);

        if ($order) {
            $qb->setSorting($order);
        }

        return $qb->getQuery();
    }
}
