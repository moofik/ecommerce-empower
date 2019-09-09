<?php

namespace App\Service\Notification;

use App\Entity\Order;

class NotificationDataResolver
{
    public function resolveOrderNotificationData(Order $order): array
    {
        $userCustomer = $order->getUserCustomer();
        $orderId = $order->getId();
        $serviceId = $order->getService()->getId();
        $userProviderId = $order->getUserProvider()->getId();
        $userCustomerId = $userCustomer->getId();
        $userCustomerAccessToken = $userCustomer->getAccessToken();
        $type = NotificationFactory::TYPE_NOTIFICATION_NEW_ORDER;

        return [
            'orderId'                 => $orderId,
            'serviceId'               => $serviceId,
            'userProviderId'          => $userProviderId,
            'userCustomerId'          => $userCustomerId,
            'userCustomerAccessToken' => $userCustomerAccessToken,
            'type'                    => $type,
        ];
    }
}
