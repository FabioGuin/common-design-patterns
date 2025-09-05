<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Classe base per eventi di dominio
 * 
 * Fornisce funzionalità comuni per tutti gli eventi:
 * - Identificazione univoca
 * - Timestamp
 * - Serializzazione
 */
abstract class DomainEvent
{
    use Dispatchable, SerializesModels;

    protected string $eventId;
    protected \DateTime $occurredAt;
    protected array $metadata = [];

    public function __construct(array $metadata = [])
    {
        $this->eventId = uniqid('event_', true);
        $this->occurredAt = new \DateTime();
        $this->metadata = $metadata;
    }

    /**
     * Restituisce l'ID univoco dell'evento
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * Restituisce il timestamp dell'evento
     */
    public function getOccurredAt(): \DateTime
    {
        return $this->occurredAt;
    }

    /**
     * Restituisce i metadati dell'evento
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Restituisce il nome della classe evento
     */
    public function getEventName(): string
    {
        return static::class;
    }

    /**
     * Restituisce una rappresentazione array dell'evento
     */
    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
            'eventName' => $this->getEventName(),
            'occurredAt' => $this->occurredAt->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata
        ];
    }

    /**
     * Restituisce una rappresentazione JSON dell'evento
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Restituisce una rappresentazione stringa dell'evento
     */
    public function __toString(): string
    {
        return $this->getEventName() . '#' . $this->eventId;
    }
}
