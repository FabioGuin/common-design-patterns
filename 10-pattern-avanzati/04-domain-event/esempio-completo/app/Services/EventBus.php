<?php

namespace App\Services;

use App\Events\DomainEvent;
use Illuminate\Support\Collection;

/**
 * Event Bus per gestire eventi e listener
 * 
 * Gestisce la pubblicazione e sottoscrizione degli eventi
 * di dominio, permettendo il disaccoppiamento tra servizi.
 */
class EventBus
{
    private array $listeners = [];
    private array $eventHistory = [];
    private int $maxHistorySize = 1000;

    /**
     * Sottoscrive un listener a un tipo di evento
     */
    public function subscribe(string $eventType, callable $listener): void
    {
        if (!isset($this->listeners[$eventType])) {
            $this->listeners[$eventType] = [];
        }
        
        $this->listeners[$eventType][] = $listener;
    }

    /**
     * Pubblica un evento a tutti i listener sottoscritti
     */
    public function publish(DomainEvent $event): void
    {
        $eventType = $event::class;
        
        // Aggiungi l'evento alla cronologia
        $this->addToHistory($event);
        
        // Pubblica l'evento a tutti i listener
        if (isset($this->listeners[$eventType])) {
            foreach ($this->listeners[$eventType] as $listener) {
                try {
                    $listener($event);
                } catch (\Exception $e) {
                    // Log dell'errore ma non bloccare gli altri listener
                    error_log("Error in listener for {$eventType}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Pubblica multiple eventi
     */
    public function publishMultiple(array $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof DomainEvent) {
                $this->publish($event);
            }
        }
    }

    /**
     * Sottoscrive un listener a multiple tipi di evento
     */
    public function subscribeToMultiple(array $eventTypes, callable $listener): void
    {
        foreach ($eventTypes as $eventType) {
            $this->subscribe($eventType, $listener);
        }
    }

    /**
     * Rimuove un listener da un tipo di evento
     */
    public function unsubscribe(string $eventType, callable $listener): void
    {
        if (isset($this->listeners[$eventType])) {
            $this->listeners[$eventType] = array_filter(
                $this->listeners[$eventType],
                function ($l) use ($listener) {
                    return $l !== $listener;
                }
            );
        }
    }

    /**
     * Rimuove tutti i listener per un tipo di evento
     */
    public function unsubscribeAll(string $eventType): void
    {
        unset($this->listeners[$eventType]);
    }

    /**
     * Restituisce tutti i listener per un tipo di evento
     */
    public function getListeners(string $eventType): array
    {
        return $this->listeners[$eventType] ?? [];
    }

    /**
     * Restituisce tutti i tipi di evento con listener
     */
    public function getEventTypes(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Restituisce la cronologia degli eventi
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    /**
     * Restituisce la cronologia degli eventi per un tipo specifico
     */
    public function getEventHistoryByType(string $eventType): array
    {
        return array_filter($this->eventHistory, function ($event) use ($eventType) {
            return $event instanceof $eventType;
        });
    }

    /**
     * Pulisce la cronologia degli eventi
     */
    public function clearHistory(): void
    {
        $this->eventHistory = [];
    }

    /**
     * Restituisce statistiche degli eventi
     */
    public function getStatistics(): array
    {
        $stats = [
            'totalEvents' => count($this->eventHistory),
            'eventTypes' => [],
            'listeners' => []
        ];

        // Conta eventi per tipo
        foreach ($this->eventHistory as $event) {
            $eventType = $event::class;
            if (!isset($stats['eventTypes'][$eventType])) {
                $stats['eventTypes'][$eventType] = 0;
            }
            $stats['eventTypes'][$eventType]++;
        }

        // Conta listener per tipo
        foreach ($this->listeners as $eventType => $listeners) {
            $stats['listeners'][$eventType] = count($listeners);
        }

        return $stats;
    }

    /**
     * Verifica se ci sono listener per un tipo di evento
     */
    public function hasListeners(string $eventType): bool
    {
        return isset($this->listeners[$eventType]) && !empty($this->listeners[$eventType]);
    }

    /**
     * Restituisce il numero di listener per un tipo di evento
     */
    public function getListenerCount(string $eventType): int
    {
        return isset($this->listeners[$eventType]) ? count($this->listeners[$eventType]) : 0;
    }

    /**
     * Aggiunge un evento alla cronologia
     */
    private function addToHistory(DomainEvent $event): void
    {
        $this->eventHistory[] = $event;
        
        // Mantieni solo gli ultimi N eventi
        if (count($this->eventHistory) > $this->maxHistorySize) {
            $this->eventHistory = array_slice($this->eventHistory, -$this->maxHistorySize);
        }
    }
}
