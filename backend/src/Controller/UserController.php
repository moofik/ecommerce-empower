<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * UserController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/api/user", methods={"GET"}, name="api_create_user")
     */
    public function createUser()
    {
        try {
            $user = new User();
            $user->setEmail('test@example.com');
            $user->setUuid(Uuid::uuid4());
            $user->setPassword(111111);
            $user->setUsername('admin'.random_int(1, 999999));
        } catch (\Exception $e) {
        }
    }
}
