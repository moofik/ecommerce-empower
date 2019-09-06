<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationFactory
{
    public const TYPE_NOTIFICATION_NEW_ORDER = 'notification_new_order';

    public const NOTIFICATION_TYPES = [
      self::TYPE_NOTIFICATION_NEW_ORDER
    ];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotificationMessageResolver
     */
    private $messageResolver;

    /**
     * NotificationFactory constructor.
     * @param EntityManagerInterface $entityManager
     * @param NotificationMessageResolver $messageResolver
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationMessageResolver $messageResolver
    ) {
        $this->entityManager = $entityManager;
        $this->messageResolver = $messageResolver;
    }

    /**
     * @param array $notificationData
     * @return Notification
     * @throws NotificationFactoryException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(array $notificationData): Notification
    {
       $type = $this->getNotificationType($notificationData);

       $notification = new Notification();
       $notification->setType($type);

       $addressee = $this->getNotificationAddressee($type, $notificationData);
       $notification->setAddressee($addressee);

       $notificationText = $this->messageResolver->getMessage($type, $addressee->getId());
       $notification->setNotificationText($notificationText);

       return $notification;
    }

    /**
     * @param array $notificationData
     * @throws NotificationFactoryException
     * @return string
     */
    private function getNotificationType(array $notificationData): string
    {
        if (!isset($notificationData['type'])) {
            throw new NotificationFactoryException('Notification type is not presented on notification data');
        }

        if (!in_array($notificationData['type'], self::NOTIFICATION_TYPES)) {
            $errorMessage = sprintf('Notification type %s does not exist', $notificationData['type']);
            throw new NotificationFactoryException($errorMessage);
        }

        return $notificationData['type'];
    }

    /**
     * @param string $type
     * @param array $notificationData
     * @return User
     * @throws \Doctrine\ORM\ORMException
     */
    private function getNotificationAddressee(string $type, array $notificationData): User
    {
        switch ($type) {
            case self::TYPE_NOTIFICATION_NEW_ORDER:
                if (!isset($notificationData['userProviderId'])) {
                    throw new NotificationFactoryException('Field userProviderId is absent');
                }

                $userProviderId = $notificationData['userProviderId'];

                return $this->entityManager->getReference(User::class, $userProviderId);
        }
    }
}
