<?php

namespace App\Services\OrderApproval;

abstract class AbstractHandler implements HandlerInterface
{
    private ?HandlerInterface $nextHandler = null;
    
    /**
     * Imposta il prossimo gestore nella catena
     */
    public function setNext(HandlerInterface $handler): HandlerInterface
    {
        $this->nextHandler = $handler;
        return $handler;
    }
    
    /**
     * Gestisce la richiesta o la passa al prossimo gestore
     */
    public function handle(OrderRequest $request): ?ApprovalResult
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($request);
        }
        
        return null;
    }
    
    /**
     * Metodo astratto che deve essere implementato dalle classi concrete
     */
    abstract protected function canHandle(OrderRequest $request): bool;
    
    /**
     * Metodo astratto che deve essere implementato dalle classi concrete
     */
    abstract protected function process(OrderRequest $request): ApprovalResult;
}
