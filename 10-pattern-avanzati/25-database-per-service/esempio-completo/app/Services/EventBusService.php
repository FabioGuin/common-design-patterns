<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EventBusService
{
    private array $subscribers = [];
    private array $eventHistory = [];

    /**
     * Sottoscrive un callback a un tipo di evento
     */
    public function subscribe(string $eventType, callable $callback): void
    {
        if (!isset($this->subscribers[$eventType])) {
            $this->subscribers[$eventType] = [];
        }
        
        $this->subscribers[$eventType][] = $callback;
        
        Log::info("Subscriber added", [
            'event_type' => $eventType,
            'total_subscribers' => count($this->subscribers[$eventType])
        ]);
    }

    /**
     * Pubblica un evento
     */
    public function publish(string $eventType, array $eventData): void
    {
        $event = [
            'id' => uniqid(),
            'type' => $eventType,
            'data' => $eventData,
            'timestamp' => now()->toISOString(),
            'published' => false
        ];

        // Aggiungi alla cronologia
        $this->eventHistory[] = $event;

        // Notifica i subscriber
        if (isset($this->subscribers[$eventType])) {
            foreach ($this->subscribers[$eventType] as $callback) {
                try {
                    $callback($event);
                    $event['published'] = true;
                } catch (\Exception $e) {
                    Log::error("Error processing event", [
                        'event_id' => $event['id'],
                        'event_type' => $eventType,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info("Event published", [
            'event_id' => $event['id'],
            'event_type' => $eventType,
            'subscribers_count' => count($this->subscribers[$eventType] ?? [])
        ]);
    }

    /**
     * Ottiene la cronologia degli eventi
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    /**
     * Ottiene gli eventi per tipo
     */
    public function getEventsByType(string $eventType): array
    {
        return array_filter($this->eventHistory, function ($event) use ($eventType) {
            return $event['type'] === $eventType;
        });
    }

    /**
     * Ottiene le statistiche degli eventi
     */
    public function getEventStats(): array
    {
        $totalEvents = count($this->eventHistory);
        $publishedEvents = count(array_filter($this->eventHistory, fn($event) => $event['published']));
        $failedEvents = $totalEvents - $publishedEvents;

        $eventTypes = array_count_values(array_column($this->eventHistory, 'type'));

        return [
            'total_events' => $totalEvents,
            'published_events' => $publishedEvents,
            'failed_events' => $failedEvents,
            'success_rate' => $totalEvents > 0 ? ($publishedEvents / $totalEvents) * 100 : 0,
            'event_types' => $eventTypes,
            'subscribers' => array_map('count', $this->subscribers)
        ];
    }

    /**
     * Pulisce la cronologia degli eventi
     */
    public function clearHistory(): void
    {
        $this->eventHistory = [];
        Log::info("Event history cleared");
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'event-bus-pattern-' . uniqid();
    }

    /**
     * Simula un evento per test
     */
    public function simulateEvent(string $eventType, array $eventData = []): void
    {
        $this->publish($eventType, $eventData);
    }

    /**
     * Ottiene i subscriber per un tipo di evento
     */
    public function getSubscribers(string $eventType): array
    {
        return $this->subscribers[$eventType] ?? [];
    }

    /**
     * Rimuove un subscriber
     */
    public function unsubscribe(string $eventType, callable $callback): bool
    {
        if (!isset($this->subscribers[$eventType])) {
            return false;
        }

        $key = array_search($callback, $this->subscribers[$eventType], true);
        if ($key !== false) {
            unset($this->subscribers[$eventType][$key]);
            $this->subscribers[$eventType] = array_values($this->subscribers[$eventType]);
            return true;
        }

        return false;
    }
}
