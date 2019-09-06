<?php

namespace App\Controller;

use App\Request\CreateUserRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     *
     * @param CreateUserRequest $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function register(
        CreateUserRequest $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = new User();

        $plainPassword = $request->getPassword();
        $password = $passwordEncoder->encodePassword($user, $plainPassword);

        $user->setPassword($password);
        $user->setUsername($request->getUsername());
        $user->setPhone($request->getPhone());
        $user->setFirstName($request->getFirstName());
        $user->setLastName($request->getLastName());
        $user->setCity($request->getCity());
        $user->setEmail($request->getEmail());
        $user->setHasAcceptedAgreement($request->hasAcceptedAgreement());

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/api/test", name="apitest", methods={"GET", "POST"})
     * @return JsonResponse
     */
    public function api()
    {
        return new JsonResponse(['status' => 'Everything will be okay. Brah!']);
    }
}
