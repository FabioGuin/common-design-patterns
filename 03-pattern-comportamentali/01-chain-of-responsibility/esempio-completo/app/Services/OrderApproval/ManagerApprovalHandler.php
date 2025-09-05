<?php

namespace App\Services\OrderApproval;

use Illuminate\Support\Facades\Log;

class ManagerApprovalHandler extends AbstractHandler
{
    private float $threshold;
    
    public function __construct()
    {
        $this->threshold = config('services.approval.manager_approval_threshold', 1000);
    }
    
    /**
     * Verifica se questo gestore può gestire la richiesta
     */
    protected function canHandle(OrderRequest $request): bool
    {
        // Gestisce solo ordini che superano la soglia del manager
        return $request->exceedsThreshold($this->threshold);
    }
    
    /**
     * Processa la richiesta di approvazione manager
     */
    protected function process(OrderRequest $request): ApprovalResult
    {
        Log::info("ManagerApprovalHandler: Processing order {$request->orderId} (Amount: {$request->totalAmount})");
        
        // Simula l'approvazione del manager
        $managerApproval = $this->simulateManagerApproval($request);
        
        if (!$managerApproval) {
            Log::warning("ManagerApprovalHandler: Manager rejected order {$request->orderId}");
            
            return ApprovalResult::rejected(
                self::class,
                'Order requires manager approval but was rejected',
                [
                    'threshold' => $this->threshold,
                    'order_amount' => $request->totalAmount,
                    'requires_manager_approval' => true
                ]
            );
        }
        
        Log::info("ManagerApprovalHandler: Order {$request->orderId} approved by manager");
        
        return ApprovalResult::approved(
            self::class,
            'Order approved by manager',
            [
                'threshold' => $this->threshold,
                'order_amount' => $request->totalAmount,
                'manager_approved' => true,
                'approval_level' => 'manager'
            ]
        );
    }
    
    /**
     * Simula l'approvazione del manager
     */
    private function simulateManagerApproval(OrderRequest $request): bool
    {
        // Simula la logica di approvazione del manager
        // In un'applicazione reale, questo potrebbe essere un database call o un'API
        
        // Per questo esempio, approva sempre se l'ordine è sotto 3000
        return $request->totalAmount < 3000;
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
        
        // Se l'approvazione del manager fallisce, ferma la catena
        if ($result->isRejected()) {
            return $result;
        }
        
        // Altrimenti, passa al prossimo gestore
        return parent::handle($request);
    }
}
