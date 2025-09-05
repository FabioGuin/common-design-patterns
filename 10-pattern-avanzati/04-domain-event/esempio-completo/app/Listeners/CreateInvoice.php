<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Services\BillingService;

/**
 * Listener per creare la fattura
 * 
 * Si iscrive all'evento OrderConfirmed e crea
 * una fattura per l'ordine.
 */
class CreateInvoice
{
    private BillingService $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Gestisce l'evento OrderConfirmed
     */
    public function handle(OrderConfirmed $event): void
    {
        $this->billingService->createInvoice(
            $event->orderId,
            $event->customerId,
            $event->total,
            $event->items
        );
    }
}
