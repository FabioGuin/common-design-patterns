<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Services\NotificationService;

/**
 * Listener per inviare email di conferma ordine
 * 
 * Si iscrive all'evento OrderConfirmed e invia
 * un'email di conferma al cliente.
 */
class SendOrderConfirmationEmail
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Gestisce l'evento OrderConfirmed
     */
    public function handle(OrderConfirmed $event): void
    {
        $this->notificationService->sendOrderConfirmationEmail(
            $event->customerId,
            $event->orderId,
            $event->total,
            $event->items
        );
    }
}
