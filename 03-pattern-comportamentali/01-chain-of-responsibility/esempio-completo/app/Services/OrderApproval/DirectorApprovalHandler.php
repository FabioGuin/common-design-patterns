<?php

namespace App\Services\OrderApproval;

use Illuminate\Support\Facades\Log;

class DirectorApprovalHandler extends AbstractHandler
{
    private float $threshold;
    
    public function __construct()
    {
        $this->threshold = config('services.approval.director_approval_threshold', 5000);
    }
    
    /**
     * Verifica se questo gestore può gestire la richiesta
     */
    protected function canHandle(OrderRequest $request): bool
    {
        // Gestisce solo ordini che superano la soglia del direttore
        return $request->exceedsThreshold($this->threshold);
    }
    
    /**
     * Processa la richiesta di approvazione direttore
     */
    protected function process(OrderRequest $request): ApprovalResult
    {
        Log::info("DirectorApprovalHandler: Processing order {$request->orderId} (Amount: {$request->totalAmount})");
        
        // Simula l'approvazione del direttore
        $directorApproval = $this->simulateDirectorApproval($request);
        
        if (!$directorApproval) {
            Log::warning("DirectorApprovalHandler: Director rejected order {$request->orderId}");
            
            return ApprovalResult::rejected(
                self::class,
                'Order requires director approval but was rejected',
                [
                    'threshold' => $this->threshold,
                    'order_amount' => $request->totalAmount,
                    'requires_director_approval' => true
                ]
            );
        }
        
        Log::info("DirectorApprovalHandler: Order {$request->orderId} approved by director");
        
        return ApprovalResult::approved(
            self::class,
            'Order approved by director',
            [
                'threshold' => $this->threshold,
                'order_amount' => $request->totalAmount,
                'director_approved' => true,
                'approval_level' => 'director'
            ]
        );
    }
    
    /**
     * Simula l'approvazione del direttore
     */
    private function simulateDirectorApproval(OrderRequest $request): bool
    {
        // Simula la logica di approvazione del direttore
        // In un'applicazione reale, questo potrebbe essere un database call o un'API
        
        // Per questo esempio, approva sempre se l'ordine è sotto 10000
        return $request->totalAmount < 10000;
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
        
        // Se l'approvazione del direttore fallisce, ferma la catena
        if ($result->isRejected()) {
            return $result;
        }
        
        // Altrimenti, passa al prossimo gestore
        return parent::handle($request);
    }
}
