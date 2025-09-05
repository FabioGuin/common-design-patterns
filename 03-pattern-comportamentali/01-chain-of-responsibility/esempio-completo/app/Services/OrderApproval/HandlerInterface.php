<?php

namespace App\Services\OrderApproval;

interface HandlerInterface
{
    /**
     * Imposta il prossimo gestore nella catena
     */
    public function setNext(HandlerInterface $handler): HandlerInterface;
    
    /**
     * Gestisce la richiesta o la passa al prossimo gestore
     */
    public function handle(OrderRequest $request): ?ApprovalResult;
}
