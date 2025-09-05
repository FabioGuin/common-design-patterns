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
 * Evento InventoryReleaseRequested per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando è richiesto il rilascio dell'inventario
 * come parte della compensazione della saga.
 */
class InventoryReleaseRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $reservationId;
    public int $productId;
    public int $quantity;
    public string $reason;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(string $reservationId, int $productId, int $quantity, string $reason, array $metadata = [])
    {
        $this->reservationId = $reservationId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->reason = $reason;
        $this->metadata = array_merge([
            'event_type' => 'InventoryReleaseRequested',
            'published_at' => now()->toISOString(),
            'saga_id' => uniqid('saga_'),
            'retry_count' => 0,
            'max_retries' => 3,
            'is_compensation' => true
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
            new PrivateChannel('inventory-events'),
            new PrivateChannel('product-events.' . $this->productId),
            new PrivateChannel('compensation-events')
        ];
    }

    /**
     * Ottiene i dati da trasmettere
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'InventoryReleaseRequested',
            'reservation_id' => $this->reservationId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
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
        return 'inventory.release.requested';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'InventoryReleaseRequested',
            'reservation_id' => $this->reservationId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
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
