<?php

namespace App\Events;

/**
 * Evento di dominio per pagamento fallito
 * 
 * Viene emesso quando un pagamento fallisce
 * e contiene tutte le informazioni necessarie
 * per notificare altri sistemi.
 */
class PaymentFailed extends DomainEvent
{
    public string $orderId;
    public string $customerId;
    public float $amount;
    public string $paymentMethod;
    public string $reason;
    public string $currency;

    public function __construct(
        string $orderId,
        string $customerId,
        float $amount,
        string $paymentMethod,
        string $reason = '',
        string $currency = 'EUR',
        array $metadata = []
    ) {
        parent::__construct($metadata);
        
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->reason = $reason;
        $this->currency = $currency;
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'amount' => $this->amount,
            'paymentMethod' => $this->paymentMethod,
            'reason' => $this->reason,
            'currency' => $this->currency
        ]);
    }
}
