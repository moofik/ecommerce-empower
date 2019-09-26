<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Shop;
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
     * @param int    $shopId
     * @param string $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Item|null
     */
    public function findOneByShopIdAndSlug($shopId, string $value): ?Item
    {
        $shop = $this->getEntityManager()->getReference(Shop::class, $shopId);

        return $this->createQueryBuilder('item')
            ->andWhere('item.slug = :val')
            ->andWhere('item.shop = :shop')
            ->setParameter('val', $value)
            ->setParameter('shop', $shop)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Shop $shop
     *
     * @return QueryBuilder
     */
    public function getFindAllByShopQueryBuilder(Shop $shop): QueryBuilder
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.shop = :shop')
            ->setParameter('shop', $shop);
    }

    /**
     * @param Shop $shop
     *
     * @return QueryBuilder
     */
    public function getCountByShopQueryBuilder(Shop $shop): QueryBuilder
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.shop = :shop')
            ->setParameter('shop', $shop)
            ->select('COUNT(item.id)');
    }
}
