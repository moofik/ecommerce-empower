<?php


namespace App\Controller\Api;


use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Service\Api\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    use ApiResponseTrait;

    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * TagController constructor.
     * @param ItemRepository $itemRepository
     * @param EntityManagerInterface $em
     * @param SerializerInterface $serializer
     */
    public function __construct(ItemRepository $itemRepository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->itemRepository = $itemRepository;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/item", methods={"POST"}, name="api_create_item")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $item = new Item();
        //@todo

        try {
            $this->em->persist($item);
            $this->em->flush();
        } catch (ORMException $e) {
        }

        $redirectUrl = $this->generateUrl('api_get_item', ['slug' => $item->getSlug()]);

        return $this->createApiResponse($item, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/item/{slug}", methods={"DELETE"}, name="api_delete_item")
     * @param string $slug
     * @return Response
     */
    public function delete(string $slug)
    {
        try {
            $result = $this->itemRepository->findOneBySlug($slug);
            $this->em->remove($result);
            $this->em->flush();
        } catch (ORMException $e) {
        }

        return $this->createApiResponse(null, 204);
    }

    /**
     * @Route("/api/items", methods={"GET"}, name="api_get_items")
     */
    public function getAll()
    {
        $items = $this->itemRepository->findAll();

        return $this->createApiResponse(['items' => $items], 200);
    }

    /**
     * @Route("/api/item/{slug}", methods={"GET"}, name="api_get_item")
     * @param string $slug
     * @return Response
     */
    public function getOne(string $slug)
    {
        try {
            $item = $this->itemRepository->findOneBySlug($slug);
        } catch (ORMException $e) {
        }

        return $this->createApiResponse($item, 200);
    }
}