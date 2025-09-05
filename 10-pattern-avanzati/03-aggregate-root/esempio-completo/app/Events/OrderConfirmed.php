<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento di dominio per ordine confermato
 * 
 * Viene emesso quando un ordine viene confermato
 * e contiene tutte le informazioni necessarie per
 * notificare altri sistemi.
 */
class OrderConfirmed
{
    use Dispatchable, SerializesModels;

    public string $orderId;
    public string $customerId;
    public float $total;
    public \DateTime $confirmedAt;

    public function __construct(string $orderId, string $customerId, float $total)
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->confirmedAt = new \DateTime();
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return [
            'event' => 'OrderConfirmed',
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'total' => $this->total,
            'confirmedAt' => $this->confirmedAt->format('Y-m-d H:i:s'),
            'timestamp' => $this->confirmedAt->getTimestamp()
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
