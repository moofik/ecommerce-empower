<?php

namespace App\Service\Notification;

use App\Repository\NotificationRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationMessageResolver
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * NotificationMessageFactory constructor.
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string $type
     * @param int $userId
     * @return string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getMessage(string $type, int $userId): ?string
    {
        switch ($type) {
            case NotificationFactory::TYPE_NOTIFICATION_NEW_ORDER:
                $countUnreadNotifications = $this->orderRepository->countUnwatchedForUserId($userId);

                if ($countUnreadNotifications === 1) {
                    return 'У вас 1 новый заказ';
                }

                return sprintf('У вас %d новых заказов', $countUnreadNotifications + 1);
        }

        return null;
    }
}
