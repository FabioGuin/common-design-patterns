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
 * Evento InventoryReserved per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando l'inventario è stato riservato
 * e può procedere con la creazione dell'ordine.
 */
class InventoryReserved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $reservationId;
    public int $productId;
    public int $quantity;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(string $reservationId, int $productId, int $quantity, array $metadata = [])
    {
        $this->reservationId = $reservationId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->metadata = array_merge([
            'event_type' => 'InventoryReserved',
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
            new PrivateChannel('inventory-events'),
            new PrivateChannel('product-events.' . $this->productId)
        ];
    }

    /**
     * Ottiene i dati da trasmettere
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'InventoryReserved',
            'reservation_id' => $this->reservationId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'inventory.reserved';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'InventoryReserved',
            'reservation_id' => $this->reservationId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
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
