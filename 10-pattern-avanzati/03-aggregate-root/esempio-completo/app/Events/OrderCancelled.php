<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento di dominio per ordine cancellato
 * 
 * Viene emesso quando un ordine viene cancellato
 * e contiene tutte le informazioni necessarie per
 * notificare altri sistemi.
 */
class OrderCancelled
{
    use Dispatchable, SerializesModels;

    public string $orderId;
    public string $customerId;
    public float $total;
    public \DateTime $cancelledAt;

    public function __construct(string $orderId, string $customerId, float $total)
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->cancelledAt = new \DateTime();
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return [
            'event' => 'OrderCancelled',
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'total' => $this->total,
            'cancelledAt' => $this->cancelledAt->format('Y-m-d H:i:s'),
            'timestamp' => $this->cancelledAt->getTimestamp()
        ];
    }

    /**
     * Restituisce una rappresentazione JSON dell'evento
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
