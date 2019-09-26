<?php


namespace App\Service\Filter;


use Doctrine\ORM\QueryBuilder;

interface FilterInterface
{
    public function apply(QueryBuilder $queryBuilder);
}