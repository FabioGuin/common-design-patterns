<?php

namespace App\Events;

/**
 * Evento di dominio per ordine spedito
 * 
 * Viene emesso quando un ordine viene spedito
 * e contiene tutte le informazioni necessarie per
 * notificare altri sistemi.
 */
class OrderShipped extends DomainEvent
{
    public string $orderId;
    public string $customerId;
    public string $trackingNumber;
    public string $carrier;
    public string $shippingAddress;
    public \DateTime $estimatedDelivery;

    public function __construct(
        string $orderId,
        string $customerId,
        string $trackingNumber,
        string $carrier = '',
        string $shippingAddress = '',
        ?\DateTime $estimatedDelivery = null,
        array $metadata = []
    ) {
        parent::__construct($metadata);
        
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->trackingNumber = $trackingNumber;
        $this->carrier = $carrier;
        $this->shippingAddress = $shippingAddress;
        $this->estimatedDelivery = $estimatedDelivery ?? new \DateTime('+3 days');
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'trackingNumber' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'shippingAddress' => $this->shippingAddress,
            'estimatedDelivery' => $this->estimatedDelivery->format('Y-m-d H:i:s')
        ]);
    }
}
