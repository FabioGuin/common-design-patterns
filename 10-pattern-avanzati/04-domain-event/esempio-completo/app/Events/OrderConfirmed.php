<?php

namespace App\Events;

/**
 * Evento di dominio per ordine confermato
 * 
 * Viene emesso quando un ordine viene confermato
 * e contiene tutte le informazioni necessarie per
 * notificare altri sistemi.
 */
class OrderConfirmed extends DomainEvent
{
    public string $orderId;
    public string $customerId;
    public float $total;
    public array $items;
    public string $shippingAddress;
    public string $billingAddress;

    public function __construct(
        string $orderId,
        string $customerId,
        float $total,
        array $items = [],
        string $shippingAddress = '',
        string $billingAddress = '',
        array $metadata = []
    ) {
        parent::__construct($metadata);
        
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->items = $items;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
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
            'items' => $this->items,
            'shippingAddress' => $this->shippingAddress,
            'billingAddress' => $this->billingAddress
        ]);
    }
}
