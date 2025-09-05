<?php

namespace App\Services\OrderApproval;

use Illuminate\Support\Facades\Log;

class CreditCheckHandler extends AbstractHandler
{
    /**
     * Verifica se questo gestore può gestire la richiesta
     */
    protected function canHandle(OrderRequest $request): bool
    {
        // Controlla se il controllo crediti è abilitato
        return config('services.approval.credit_check_enabled', true);
    }
    
    /**
     * Processa la richiesta di controllo crediti
     */
    protected function process(OrderRequest $request): ApprovalResult
    {
        Log::info("CreditCheckHandler: Processing order {$request->orderId}");
        
        // Simula il controllo crediti
        if (!$request->hasSufficientCredit()) {
            Log::warning("CreditCheckHandler: Insufficient credit for order {$request->orderId}");
            
            return ApprovalResult::rejected(
                self::class,
                "Insufficient credit. Required: {$request->totalAmount}, Available: {$request->customerCredit}",
                [
                    'required_amount' => $request->totalAmount,
                    'available_credit' => $request->customerCredit,
                    'deficit' => $request->totalAmount - $request->customerCredit
                ]
            );
        }
        
        Log::info("CreditCheckHandler: Order {$request->orderId} passed credit check");
        
        return ApprovalResult::approved(
            self::class,
            'Credit check passed',
            [
                'required_amount' => $request->totalAmount,
                'available_credit' => $request->customerCredit,
                'remaining_credit' => $request->customerCredit - $request->totalAmount
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
        
        // Se il controllo crediti fallisce, ferma la catena
        if ($result->isRejected()) {
            return $result;
        }
        
        // Altrimenti, passa al prossimo gestore
        return parent::handle($request);
    }
}
