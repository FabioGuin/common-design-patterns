<?php

namespace App\Events;

/**
 * Evento di dominio per ordine cancellato
 * 
 * Viene emesso quando un ordine viene cancellato
 * e contiene tutte le informazioni necessarie per
 * notificare altri sistemi.
 */
class OrderCancelled extends DomainEvent
{
    public string $orderId;
    public string $customerId;
    public float $total;
    public string $reason;
    public array $items;

    public function __construct(
        string $orderId,
        string $customerId,
        float $total,
        string $reason = '',
        array $items = [],
        array $metadata = []
    ) {
        parent::__construct($metadata);
        
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->reason = $reason;
        $this->items = $items;
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'total' => $this->total,
            'reason' => $this->reason,
            'items' => $this->items
        ]);
    }
}
