<?php


namespace App\Service\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * {@inheritdoc}
 *
 * Skeleton class for filter implementation.
 *
 * @author Alexander Orlovsky <moofik12@gmail.com>
 */
class AbstractFilter implements FilterInterface
{
    public function apply(QueryBuilder $queryBuilder)
    {

    }

    protected function extractProperties()
    {

    }
}