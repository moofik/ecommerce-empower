<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenStorageFillerSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_AUTHENTICATED => [
                ['storeJwtToken', 1000],
            ],
        ];
    }

    /**
     * @param JWTAuthenticatedEvent $event
     */
    public function storeJwtToken(JWTAuthenticatedEvent $event)
    {
        $token = $event->getToken();
        $this->tokenStorage->setToken($token);
    }
}