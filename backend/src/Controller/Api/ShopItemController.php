<?php

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\Shop;
use App\Entity\User;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use App\Repository\ShopRepository;
use App\Serializer\Groups\GroupsResolver;
use App\Service\Api\DefaultApiActionsTrait;
use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use App\Service\Item\TagBinder;
use App\Service\Pagination\PaginatedCollectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ItemController.
 */
class ShopItemController extends AbstractController
{
    use DefaultApiActionsTrait;

    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var GroupsResolver
     */
    private $groupsResolver;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * ItemController constructor.
     *
     * @param ItemRepository $itemRepository
     * @param ShopRepository $shopRepository
     * @param EntityManagerInterface $em
     * @param SerializerInterface $serializer
     * @param GroupsResolver $groupsResolver
     */
    public function __construct(
        ItemRepository $itemRepository,
        ShopRepository $shopRepository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        GroupsResolver $groupsResolver
    ) {
        $this->itemRepository = $itemRepository;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->groupsResolver = $groupsResolver;
        $this->shopRepository = $shopRepository;
    }

    /**
     * @Route("/api/shops/{shopId}/items", methods={"POST"}, name="api_create_item")
     *
     * @param int $shopId
     * @param Request $request
     * @param TagBinder $binder
     *
     * @param ShopRepository $shopRepository
     * @return Response
     * @throws ORMException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function create(int $shopId, Request $request, TagBinder $binder, ShopRepository $shopRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $shop = $shopRepository->find($shopId);

        if ($shop->getUser()->getId() !== $user->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');
        }

        $item = new Item();
        $this->fillEntityFromRequest($request, $item, ItemType::class);
        $item->setShop($shop);
        $binder->bindTags($item, $request->get('tags', []));

        $this->saveEntity($this->em, $item);

        $redirectUrl = $this->generateUrl('api_get_item', ['shopId' => $shopId, 'slug' => $item->getSlug()]);

        return $this->createApiResponse($item, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/shops/{shopId}/items/{slug}", methods={"DELETE"}, name="api_delete_item")
     *
     * @param int $shopId
     * @param string $slug
     *
     * @return Response
     */
    public function delete(int $shopId, string $slug)
    {
        try {
            $item = $this->itemRepository->findOneByShopIdAndSlug($shopId, $slug);

            if ($item === null) {
                throw new NotFoundHttpException(sprintf('Item with slug %s was not found in shop %s', $slug, $shopId));
            }

            $this->em->remove($item);
            $this->em->flush();
        } catch (ORMException $e) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        return $this->createApiResponse(null, 204);
    }

    /**
     * @Route("/api/shops/{shopId}/items", methods={"GET"}, name="api_get_items")
     *
     * @param int $shopId
     * @param Request                    $request
     * @param PaginatedCollectionFactory $factory
     *
     * @return Response
     */
    public function getAll(int $shopId, Request $request, PaginatedCollectionFactory $factory)
    {
        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        try {
            /** @var Shop $shop */
            $shop = $this->em->getReference(Shop::class, $shopId);
        } catch (ORMException $exception) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        $qb = $this->itemRepository->getFindAllByShopQueryBuilder($shop);
        $qbCount = $this->itemRepository->getCountByShopQueryBuilder($shop);
        $collection = $factory->createCollection($qb, $qbCount, 'api_get_items', ['shopId' => $shopId], $page, $count);

        return $this->createApiResponse($collection, 200);
    }

    /**
     * @Route("/api/shops/{shopId}/items/{slug}", methods={"GET"}, name="api_get_item")
     *
     * @param string $shopId
     * @param string $slug
     *
     * @return Response
     */
    public function getOne(string $shopId, string $slug)
    {
        try {
            $item = $this->itemRepository->findOneByShopIdAndSlug($shopId, $slug);
        } catch (ORMException $e) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        if ($item === null) {
            throw new NotFoundHttpException(sprintf('Item with slug %s was not found', $slug));
        }

        return $this->createApiResponse($item, 200);
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @return GroupsResolver
     */
    public function getGroupsResolver(): GroupsResolver
    {
        return $this->groupsResolver;
    }
}
