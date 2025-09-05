<?php

namespace App\Services\OrderApproval;

use Illuminate\Support\Facades\Log;

class InventoryCheckHandler extends AbstractHandler
{
    /**
     * Verifica se questo gestore può gestire la richiesta
     */
    protected function canHandle(OrderRequest $request): bool
    {
        // Controlla se il controllo inventario è abilitato
        return config('services.approval.inventory_check_enabled', true);
    }
    
    /**
     * Processa la richiesta di controllo inventario
     */
    protected function process(OrderRequest $request): ApprovalResult
    {
        Log::info("InventoryCheckHandler: Processing order {$request->orderId}");
        
        // Simula il controllo inventario
        if (!$request->allItemsAvailable()) {
            $unavailableItems = [];
            
            foreach ($request->items as $index => $item) {
                if (!isset($item['available']) || !$item['available']) {
                    $unavailableItems[] = $item['name'] ?? "Item {$index}";
                }
            }
            
            Log::warning("InventoryCheckHandler: Unavailable items for order {$request->orderId}", [
                'unavailable_items' => $unavailableItems
            ]);
            
            return ApprovalResult::rejected(
                self::class,
                'Some items are not available in inventory',
                [
                    'unavailable_items' => $unavailableItems,
                    'total_items' => count($request->items)
                ]
            );
        }
        
        Log::info("InventoryCheckHandler: Order {$request->orderId} passed inventory check");
        
        return ApprovalResult::approved(
            self::class,
            'Inventory check passed',
            [
                'total_items' => count($request->items),
                'all_items_available' => true
            ]
        );
    }
    
    /**
     * Gestisce la richiesta con la logica della catena
     */
    public function handle(OrderRequest $request): ?ApprovalResult
    {
        if (!$this->canHandle($request)) {
            return parent::handle($request);
        }
        
        $result = $this->process($request);
        
        // Se il controllo inventario fallisce, ferma la catena
        if ($result->isRejected()) {
            return $result;
        }
        
        // Altrimenti, passa al prossimo gestore
        return parent::handle($request);
    }
}
