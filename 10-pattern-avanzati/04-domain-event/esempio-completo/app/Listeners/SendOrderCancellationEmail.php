<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\NotificationService;

/**
 * Listener per inviare email di cancellazione ordine
 * 
 * Si iscrive all'evento OrderCancelled e invia
 * un'email di cancellazione al cliente.
 */
class SendOrderCancellationEmail
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Gestisce l'evento OrderCancelled
     */
    public function handle(OrderCancelled $event): void
    {
        $this->notificationService->sendOrderCancellationEmail(
            $event->customerId,
            $event->orderId,
            $event->total,
            $event->reason
        );
    }
}
