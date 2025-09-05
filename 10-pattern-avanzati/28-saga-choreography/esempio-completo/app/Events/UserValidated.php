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
 * Evento UserValidated per il Saga Choreography Pattern
 * 
 * Questo evento viene pubblicato quando un utente è stato validato
 * e può procedere con la creazione di un ordine.
 */
class UserValidated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $userName;
    public string $userEmail;
    public array $metadata;
    public string $eventId;

    /**
     * Crea una nuova istanza dell'evento
     */
    public function __construct(int $userId, string $userName, string $userEmail, array $metadata = [])
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->metadata = array_merge([
            'event_type' => 'UserValidated',
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
            'event_type' => 'UserValidated',
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Ottiene il nome dell'evento da trasmettere
     */
    public function broadcastAs(): string
    {
        return 'user.validated';
    }

    /**
     * Ottiene i dati dell'evento per il logging
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => 'UserValidated',
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
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
