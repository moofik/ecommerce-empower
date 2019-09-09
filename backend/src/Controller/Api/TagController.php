<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use App\Service\Api\ApiResponseTrait;
use App\Service\Api\FormHandlerTrait;
use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializerInterface;
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
    use ApiResponseTrait, FormHandlerTrait;

    /**
     * @var TagRepository
     */
    private $tagRepository;

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
     *
     * @param TagRepository          $tagRepository
     * @param EntityManagerInterface $em
     * @param SerializerInterface    $serializer
     */
    public function __construct(TagRepository $tagRepository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->tagRepository = $tagRepository;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/tag", methods={"POST"}, name="api_create_tag")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);

            return $this->createValidationErrorResponse($errors);
        }

        try {
            $this->em->persist($tag);
            $this->em->flush();
        } catch (ORMException $e) {
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        $redirectUrl = $this->generateUrl('api_get_tag', ['slug' => $tag->getSlug()]);

        return $this->createApiResponse($tag, 201, ['Location' => $redirectUrl]);
    }

    /**
     * @Route("/api/tag/{slug}", methods={"DELETE"}, name="api_delete_tag")
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

        if ($tag === null) {
            throw new NotFoundHttpException(sprintf('Tag with slug %s was not found', $slug));
        }

        return $this->createApiResponse(null, 204);
    }

    /**
     * @Route("/api/tags", methods={"GET"}, name="api_get_tags")
     */
    public function getAll()
    {
        $tags = $this->tagRepository->findAll();

        return $this->createApiResponse(['items' => $tags], 200);
    }

    /**
     * @Route("/api/tag/{slug}", methods={"GET"}, name="api_get_tag")
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
            $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

            throw new ApiProblemException($problem);
        }

        if ($tag === null) {
            throw new NotFoundHttpException(sprintf('Tag with slug %s was not found', $slug));
        }

        return $this->createApiResponse($tag, 200);
    }
}
