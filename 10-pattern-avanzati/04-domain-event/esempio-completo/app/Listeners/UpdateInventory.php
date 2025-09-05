<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Services\InventoryService;

/**
 * Listener per aggiornare l'inventario
 * 
 * Si iscrive all'evento OrderConfirmed e aggiorna
 * l'inventario dei prodotti venduti.
 */
class UpdateInventory
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Gestisce l'evento OrderConfirmed
     */
    public function handle(OrderConfirmed $event): void
    {
        foreach ($event->items as $item) {
            $this->inventoryService->decreaseStock(
                $item['productId'],
                $item['quantity']
            );
        }
    }
}
