<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * @param string $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Item|null
     */
    public function findOneBySlug(string $value): ?Item
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.slug = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return QueryBuilder
     */
    public function findAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('item');
    }

    /**
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('item')
            ->select('COUNT(item.id)');
    }
}
