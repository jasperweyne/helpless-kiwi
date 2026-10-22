<?php

namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;

/**
 * Applies a case-insensitive LIKE search across one or more query builder fields.
 */
class SearchFilter
{
    /**
     * @param string[] $fields Fully qualified fields (e.g. 'p.name') to match the search term against
     */
    public static function apply(QueryBuilder $qb, string $search, array $fields): void
    {
        $conditions = $qb->expr()->orX();
        foreach ($fields as $field) {
            $conditions->add($qb->expr()->like($field, ':search'));
        }

        $qb->andWhere($conditions)->setParameter('search', '%'.$search.'%');
    }
}
