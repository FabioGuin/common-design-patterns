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
 * Evento NotificationSent per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando una notifica è stata inviata
 * e completa la saga di creazione ordine.
 */
class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $notificationId;
    public int $userId;
    public string $message;
    public string $type;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(int $notificationId, int $userId, string $message, string $type = 'order_confirmation', array $metadata = [])
    {
        $this->notificationId = $notificationId;
        $this->userId = $userId;
        $this->message = $message;
        $this->type = $type;
        $this->metadata = array_merge([
            'event_type' => 'NotificationSent',
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
            new PrivateChannel('notification-events'),
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
            'event_type' => 'NotificationSent',
            'notification_id' => $this->notificationId,
            'user_id' => $this->userId,
            'message' => $this->message,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'NotificationSent',
            'notification_id' => $this->notificationId,
            'user_id' => $this->userId,
            'message' => $this->message,
            'type' => $this->type,
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
