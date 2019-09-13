<?php

namespace App\Service\Pagination\Pagerfanta\Adapter;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

class NonIterableDoctrineORMAdapter implements AdapterInterface
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var QueryBuilder
     */
    private $countQueryBuilder;

    /**
     * Custom ORM Adapter constructor.
     *
     * @param QueryBuilder $queryBuilder      Query builder for the query that returns the collection of items
     * @param QueryBuilder $countQueryBuilder Query builder for the query that returns total number of items
     */
    public function __construct(QueryBuilder $queryBuilder, QueryBuilder $countQueryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->countQueryBuilder = $countQueryBuilder;
    }

    /**
     * Returns the number of results.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     *
     * @return int The number of results.
     */
    public function getNbResults()
    {
        return $this->countQueryBuilder
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        return $this->queryBuilder
            ->setMaxResults($length)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
