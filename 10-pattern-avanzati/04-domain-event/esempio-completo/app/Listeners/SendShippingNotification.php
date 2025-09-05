<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Services\NotificationService;

/**
 * Listener per inviare notifica di spedizione
 * 
 * Si iscrive all'evento OrderShipped e invia
 * una notifica di spedizione al cliente.
 */
class SendShippingNotification
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Gestisce l'evento OrderShipped
     */
    public function handle(OrderShipped $event): void
    {
        $this->notificationService->sendShippingNotification(
            $event->customerId,
            $event->orderId,
            $event->trackingNumber,
            $event->carrier,
            $event->estimatedDelivery
        );
    }
}
