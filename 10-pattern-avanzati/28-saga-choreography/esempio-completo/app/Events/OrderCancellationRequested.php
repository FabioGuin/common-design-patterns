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
 * Evento OrderCancellationRequested per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando è richiesta la cancellazione di un ordine
 * e avvia la saga di cancellazione.
 */
class OrderCancellationRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $orderId;
    public int $userId;
    public string $reason;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(int $orderId, int $userId, string $reason, array $metadata = [])
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->reason = $reason;
        $this->metadata = array_merge([
            'event_type' => 'OrderCancellationRequested',
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
            'event_type' => 'OrderCancellationRequested',
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'order.cancellation.requested';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'OrderCancellationRequested',
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'reason' => $this->reason,
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
