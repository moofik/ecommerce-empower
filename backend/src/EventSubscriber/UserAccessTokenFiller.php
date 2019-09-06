<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class UserAccessTokenFiller implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UserAccessTokenFiller constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_CREATED => [
                ['onJWTCreated', 0]
            ]
        ];
    }

    /**
     * @param JWTCreatedEvent $event
     * @throws \Exception
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof User) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $user->setAccessToken($token);
            $this->entityManager->flush();
        }
    }
}