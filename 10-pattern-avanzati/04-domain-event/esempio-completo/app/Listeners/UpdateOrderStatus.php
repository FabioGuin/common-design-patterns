<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Events\PaymentProcessed;
use App\Events\PaymentFailed;
use App\Services\OrderService;

/**
 * Listener per aggiornare lo status dell'ordine
 * 
 * Si iscrive a multiple eventi e aggiorna
 * lo status dell'ordine di conseguenza.
 */
class UpdateOrderStatus
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Gestisce l'evento OrderShipped
     */
    public function handleOrderShipped(OrderShipped $event): void
    {
        $this->orderService->updateStatus(
            $event->orderId,
            'SHIPPED',
            [
                'trackingNumber' => $event->trackingNumber,
                'carrier' => $event->carrier,
                'shippedAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Gestisce l'evento PaymentProcessed
     */
    public function handlePaymentProcessed(PaymentProcessed $event): void
    {
        $this->orderService->updateStatus(
            $event->orderId,
            'PAID',
            [
                'paymentMethod' => $event->paymentMethod,
                'transactionId' => $event->transactionId,
                'paidAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Gestisce l'evento PaymentFailed
     */
    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $this->orderService->updateStatus(
            $event->orderId,
            'PAYMENT_FAILED',
            [
                'paymentMethod' => $event->paymentMethod,
                'failureReason' => $event->reason,
                'failedAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
            ]
        );
    }
}
