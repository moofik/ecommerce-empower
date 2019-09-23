<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\UserType;
use App\Serializer\Groups\GroupsResolver;
use App\Service\Api\DefaultApiActionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    use DefaultApiActionsTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var GroupsResolver
     */
    private $groupsResolver;

    /**
     * RegistrationController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param GroupsResolver $groupsResolver
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        GroupsResolver $groupsResolver
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->groupsResolver = $groupsResolver;
    }

    /**
     * @Route("/api/register", methods={"POST"})
     *
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $this->fillEntityFromRequest($request, $user, UserType::class);
        $user->setPassword($encoder->encodePassword($user, $request->get('password')));
        $this->saveEntity($this->entityManager, $user);

        return $this->createApiResponse($user, 201);
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
