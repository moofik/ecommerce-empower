<?php

namespace App\Tests\Feature\Context;


use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthorizationContext implements Context
{
    use DatabaseAwareContextTrait, RestContextAwareTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * AuthorizationContext constructor.
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    /**
     * @var string $username
     * @var string $password
     *
     * @Given there is an user with username :username and password :password
     */
    public function thereIsAnUserWithUsernameAndPassword(string $username, string $password)
    {
        $user = new User();
        $user->setUsername($username);
        $password = $this->encoder->encodePassword($user, $password);
        $user->setPassword($password);
        $user->setEmail('test@colloseum.com');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
