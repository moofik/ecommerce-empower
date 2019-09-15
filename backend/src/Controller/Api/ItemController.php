<?php

namespace App\Controller\Api;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use App\Service\Api\DefaultApiActionsTrait;
use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
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
class ItemController extends AbstractController
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
     * ItemController constructor.
     *
     * @param ItemRepository         $itemRepository
     * @param EntityManagerInterface $em
     * @param SerializerInterface    $serializer
     */
    public function __construct(ItemRepository $itemRepository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->itemRepository = $itemRepository;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/item", methods={"POST"}, name="api_create_item")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $item = new Item();
        $this->fillEntityFromRequest($request, $item, ItemType::class);
        $this->saveEntity($this->em, $item);

        $redirectUrl = $this->generateUrl('api_get_item', ['slug' => $item->getSlug()]);

        return $this->createApiResponse($item, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/item/{slug}", methods={"DELETE"}, name="api_delete_item")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function delete(string $slug)
    {
        try {
            $item = $this->itemRepository->findOneBySlug($slug);
            $this->em->remove($item);
            $this->em->flush();
        } catch (ORMException $e) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        if ($item === null) {
            throw new NotFoundHttpException(sprintf('Item with slug %s was not found', $slug));
        }

        return $this->createApiResponse(null, 204);
    }

    /**
     * @Route("/api/items", methods={"GET"}, name="api_get_items")
     *
     * @param Request                    $request
     * @param PaginatedCollectionFactory $factory
     *
     * @return Response
     */
    public function getAll(Request $request, PaginatedCollectionFactory $factory)
    {
        $page = $request->query->get('page', 1);
        $qb = $this->itemRepository->findAllQueryBuilder();
        $qbCount = $this->itemRepository->getCountQueryBuilder();
        $collection = $factory->createCollection($qb, $qbCount, 'api_get_items', [], $page, 10);

        return $this->createApiResponse($collection, 200);
    }

    /**
     * @Route("/api/item/{slug}", methods={"GET"}, name="api_get_item")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function getOne(string $slug)
    {
        try {
            $item = $this->itemRepository->findOneBySlug($slug);
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
}
