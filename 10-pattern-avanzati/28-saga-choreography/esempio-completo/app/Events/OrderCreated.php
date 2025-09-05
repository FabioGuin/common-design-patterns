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
 * Evento OrderCreated per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando un ordine è stato creato
 * e può procedere con il processamento del pagamento.
 */
class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $orderId;
    public int $userId;
    public float $total;
    public string $status;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(int $orderId, int $userId, float $total, string $status = 'pending', array $metadata = [])
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->total = $total;
        $this->status = $status;
        $this->metadata = array_merge([
            'event_type' => 'OrderCreated',
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
            new PrivateChannel('order-events'),
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
            'event_type' => 'OrderCreated',
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'total' => $this->total,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'OrderCreated',
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'total' => $this->total,
            'status' => $this->status,
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
