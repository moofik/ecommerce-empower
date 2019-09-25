<?php


namespace App\Service\Item;


use App\Entity\Item;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagBinder
{
    /**
     * @var TagRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TagBinder constructor.
     * @param TagRepository $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TagRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Item $item
     * @param string $tagName
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function bindTag(Item $item, string $tagName)
    {
        if ($tag = $this->repository->findOneByName($tagName)) {
            $item->addTag($tag);
        }
    }

    /**
     * @param Item $item
     * @param array $tagNames
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function bindTags(Item $item, array $tagNames)
    {
        foreach ($tagNames as $tagName) {
            if ($tag = $this->repository->findOneByName($tagName)) {
                $item->addTag($tag);
            }
        }
    }

    /**
     * @param Item $item
     */
    public function unbindAllTags(Item $item)
    {
        $builder = $this->entityManager
            ->getConnection()
            ->createQueryBuilder()
            ->delete('tag_item')
            ->where('item_id = :id')
            ->setParameter('id', $item->getId());

        $builder->execute();
    }
}