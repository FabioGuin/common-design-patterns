<?php

namespace App\Services\OrderApproval;

use Illuminate\Support\Facades\Log;

class ValidationHandler extends AbstractHandler
{
    /**
     * Verifica se questo gestore può gestire la richiesta
     */
    protected function canHandle(OrderRequest $request): bool
    {
        // Il gestore di validazione gestisce sempre le richieste
        return true;
    }
    
    /**
     * Processa la richiesta di validazione
     */
    protected function process(OrderRequest $request): ApprovalResult
    {
        Log::info("ValidationHandler: Processing order {$request->orderId}");
        
        // Validazione base
        if (empty($request->customerName)) {
            return ApprovalResult::rejected(
                self::class,
                'Customer name is required'
            );
        }
        
        if (empty($request->customerEmail) || !filter_var($request->customerEmail, FILTER_VALIDATE_EMAIL)) {
            return ApprovalResult::rejected(
                self::class,
                'Valid customer email is required'
            );
        }
        
        if ($request->totalAmount <= 0) {
            return ApprovalResult::rejected(
                self::class,
                'Total amount must be greater than 0'
            );
        }
        
        if (empty($request->items)) {
            return ApprovalResult::rejected(
                self::class,
                'Order must contain at least one item'
            );
        }
        
        // Validazione degli item
        foreach ($request->items as $index => $item) {
            if (!isset($item['name']) || empty($item['name'])) {
                return ApprovalResult::rejected(
                    self::class,
                    "Item at index {$index} must have a name"
                );
            }
            
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                return ApprovalResult::rejected(
                    self::class,
                    "Item at index {$index} must have a positive quantity"
                );
            }
            
            if (!isset($item['price']) || $item['price'] < 0) {
                return ApprovalResult::rejected(
                    self::class,
                    "Item at index {$index} must have a non-negative price"
                );
            }
        }
        
        Log::info("ValidationHandler: Order {$request->orderId} passed validation");
        
        return ApprovalResult::approved(
            self::class,
            'Order validation passed',
            ['validated_at' => now()->toISOString()]
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
        
        // Se la validazione fallisce, ferma la catena
        if ($result->isRejected()) {
            return $result;
        }
        
        // Altrimenti, passa al prossimo gestore
        return parent::handle($request);
    }
}
