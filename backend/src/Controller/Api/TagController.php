<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use App\Serializer\Groups\GroupsResolver;
use App\Service\Api\DefaultApiActionsTrait;
use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use App\Service\Pagination\PaginatedCollectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController.
 */
class TagController extends AbstractController
{
    use DefaultApiActionsTrait;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var GroupsResolver
     */
    private $groupsResolver;

    /**
     * TagController constructor.
     *
     * @param TagRepository $tagRepository
     * @param EntityManagerInterface $em
     * @param SerializerInterface $serializer
     * @param GroupsResolver $groupsResolver
     */
    public function __construct(
        TagRepository $tagRepository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        GroupsResolver $groupsResolver
    ) {
        $this->tagRepository = $tagRepository;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->groupsResolver = $groupsResolver;
    }

    /**
     * @Route("/api/tags", methods={"POST"}, name="api_create_tag")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $tag = new Tag();
        $this->fillEntityFromRequest($request, $tag, TagType::class);
        $this->saveEntity($this->em, $tag);

        $redirectUrl = $this->generateUrl('api_get_tag', ['slug' => $tag->getSlug()]);

        return $this->createApiResponse($tag, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/tags/{slug}", methods={"DELETE"}, name="api_delete_tag")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param string $slug
     *
     * @return Response
     */
    public function delete(string $slug)
    {
        try {
            $tag = $this->tagRepository->findOneBySlug($slug);
            $this->em->remove($tag);
            $this->em->flush();
        } catch (ORMException $e) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        return $this->createApiResponse(null, 204);
    }

    /**
     * @Route("/api/tags", methods={"GET"}, name="api_get_tags")
     *
     * @param Request                    $request
     * @param PaginatedCollectionFactory $factory
     *
     * @return Response
     */
    public function getAll(Request $request, PaginatedCollectionFactory $factory)
    {
        $page = $request->query->get('page', 1);
        $qb = $this->tagRepository->findAllQueryBuilder();
        $qbCount = $this->tagRepository->getCountQueryBuilder();
        $collection = $factory->createCollection($qb, $qbCount, 'api_get_tags', [], $page, 10);

        return $this->createApiResponse($collection, 200);
    }

    /**
     * @Route("/api/tags/{slug}", methods={"GET"}, name="api_get_tag")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function getOne(string $slug)
    {
        try {
            $tag = $this->tagRepository->findOneBySlug($slug);
        } catch (ORMException $e) {
            $this->throwDatabaseApiException($e->getMessage());
        }

        if ($tag === null) {
            throw new NotFoundHttpException(sprintf('Tag with slug %s was not found', $slug));
        }

        return $this->createApiResponse($tag, 200);
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
