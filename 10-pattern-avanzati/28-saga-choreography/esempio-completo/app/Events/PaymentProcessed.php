<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento PaymentProcessed per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando un pagamento è stato processato
 * e può procedere con l'invio della notifica.
 */
class PaymentProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $paymentId;
    public int $orderId;
    public int $userId;
    public float $amount;
    public string $status;
    public string $transactionId;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(int $paymentId, int $orderId, int $userId, float $amount, string $status, string $transactionId = null, array $metadata = [])
    {
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->amount = $amount;
        $this->status = $status;
        $this->transactionId = $transactionId;
        $this->metadata = array_merge([
            'event_type' => 'PaymentProcessed',
            'published_at' => now()->toISOString(),
            'saga_id' => uniqid('saga_'),
            'retry_count' => 0,
            'max_retries' => 3
        ], $metadata);
        $this->eventId = 'event_' . uniqid();
    }

    /**
     * Ottiene i canali su cui l'evento deve essere trasmesso
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('saga-events'),
            new PrivateChannel('payment-events'),
            new PrivateChannel('order-events.' . $this->orderId),
            new PrivateChannel('user-events.' . $this->userId)
        ];
    }

    /**
     * Ottiene i dati da trasmettere
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'PaymentProcessed',
            'payment_id' => $this->paymentId,
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_id' => $this->transactionId,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'payment.processed';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'PaymentProcessed',
            'payment_id' => $this->paymentId,
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'status' => $this->status,
            'transaction_id' => $this->transactionId,
            'metadata' => $this->metadata,
            'published_at' => now()->toISOString()
        ];
    }

    /**
     * Ottiene i dati dell'evento per la serializzazione
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
