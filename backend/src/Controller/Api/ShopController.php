<?php

namespace App\Controller\Api;

use App\Entity\Shop;
use App\Entity\User;
use App\Repository\ShopRepository;
use App\Serializer\Groups\GroupsResolver;
use App\Service\Api\DefaultApiActionsTrait;
use App\Service\Shop\Maker\ShopMaker;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ShopController extends AbstractController
{
    use DefaultApiActionsTrait;

    /**
     * @var GroupsResolver
     */
    private $groupsResolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * ShopController constructor.
     *
     * @param GroupsResolver         $groupsResolver
     * @param SerializerInterface    $serializer
     * @param EntityManagerInterface $em
     * @param ShopRepository         $shopRepository
     */
    public function __construct(
        GroupsResolver $groupsResolver,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ShopRepository $shopRepository
    ) {
        $this->groupsResolver = $groupsResolver;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->shopRepository = $shopRepository;
    }

    /**
     * @Route("/api/shops", methods={"POST"}, name="api_create_shop")
     *
     * @param ShopMaker $maker
     * @IsGranted({"ROLE_USER", "ROLE_ADMIN"})
     *
     * @return Response
     */
    public function createShop(ShopMaker $maker)
    {
        /** @var User $user */
        $user = $this->getUser();
        $shop = $maker->create($user);

        $this->saveEntity($this->getEntityManager(), $shop);

        $redirectUrl = $this->generateUrl('api_get_shop', ['shop' => $shop->getId()]);

        return $this->createApiResponse($shop, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/shops/{shop}", methods={"GET"}, name="api_get_shop")
     *
     * @param Shop $shop
     *
     * @return Response
     */
    public function getShop(Shop $shop): Response
    {
        return $this->createApiResponse($shop, 200);
    }

    /**
     * @Route("/api/shops/{shop}", methods={"DELETE"}, name="api_delete_shop")
     * @IsGranted("SHOP_MANAGE", subject="shop")
     *
     * @param Shop                          $shop
     * @param AuthorizationCheckerInterface $authorizationChecker
     *
     * @return Response
     */
    public function deleteShop(Shop $shop, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        try {
            $this->em->remove($shop);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->throwDatabaseApiException($e->getMessage());
        }

        return $this->createApiResponse(null, 204);
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

    public function getEntityManager(): EntityManagerInterface
    {
        if (!$this->em->isOpen()) {
            $this->em->clear();
        }

        return $this->em;
    }
}
