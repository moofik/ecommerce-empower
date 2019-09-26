<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @param string $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Tag|null
     */
    public function findOneBySlug(string $value): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Tag|null
     */
    public function findOneByName(string $value): ?Tag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return QueryBuilder
     */
    public function findAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('tag');
    }

    /**
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('tag')
            ->select('COUNT(tag.id)');
    }
}
