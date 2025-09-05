<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Services\InventoryService;

/**
 * Listener per ripristinare l'inventario
 * 
 * Si iscrive all'evento OrderCancelled e ripristina
 * l'inventario dei prodotti cancellati.
 */
class RestoreInventory
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Gestisce l'evento OrderCancelled
     */
    public function handle(OrderCancelled $event): void
    {
        foreach ($event->items as $item) {
            $this->inventoryService->increaseStock(
                $item['productId'],
                $item['quantity']
            );
        }
    }
}
