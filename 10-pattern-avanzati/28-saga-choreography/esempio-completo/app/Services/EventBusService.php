<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione dell'Event Bus
 * 
 * Questo servizio gestisce la pubblicazione e sottoscrizione di eventi
 * per il Saga Choreography Pattern.
 */
class EventBusService
{
    private string $id;
    private array $subscribers;
    private array $eventHistory;
    private array $eventStats;
    private int $totalEvents;
    private int $failedEvents;

    public function __construct()
    {
        $this->id = 'event-bus-' . uniqid();
        $this->subscribers = [];
        $this->eventHistory = [];
        $this->eventStats = [
            'total_events' => 0,
            'published_events' => 0,
            'failed_events' => 0,
            'duplicate_events' => 0,
            'retry_events' => 0
        ];
        $this->totalEvents = 0;
        $this->failedEvents = 0;
        
        Log::info('EventBusService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sottoscrive un listener a un evento
     */
    public function subscribe(string $eventType, callable $listener): void
    {
        if (!isset($this->subscribers[$eventType])) {
            $this->subscribers[$eventType] = [];
        }
        
        $this->subscribers[$eventType][] = $listener;
        
        Log::info('Event listener subscribed', [
            'event_type' => $eventType,
            'listener_count' => count($this->subscribers[$eventType]),
            'event_bus' => $this->id
        ]);
    }

    /**
     * Pubblica un evento
     */
    public function publish(string $eventType, array $data, array $metadata = []): array
    {
        $this->totalEvents++;
        $this->eventStats['total_events']++;
        
        $eventId = 'event_' . uniqid();
        $event = [
            'id' => $eventId,
            'type' => $eventType,
            'data' => $data,
            'metadata' => array_merge([
                'published_at' => now()->toISOString(),
                'event_bus' => $this->id,
                'retry_count' => 0,
                'max_retries' => 3
            ], $metadata)
        ];
        
        try {
            Log::info('Publishing event', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'event_bus' => $this->id
            ]);
            
            // Verifica se ci sono listener per questo tipo di evento
            if (!isset($this->subscribers[$eventType])) {
                Log::warning('No listeners for event type', [
                    'event_type' => $eventType,
                    'event_id' => $eventId
                ]);
                return $event;
            }
            
            // Esegue tutti i listener
            $results = [];
            foreach ($this->subscribers[$eventType] as $listener) {
                try {
                    $result = $listener($event);
                    $results[] = [
                        'listener' => get_class($listener),
                        'result' => $result,
                        'success' => true
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        'listener' => get_class($listener),
                        'error' => $e->getMessage(),
                        'success' => false
                    ];
                    
                    Log::error('Event listener failed', [
                        'event_id' => $eventId,
                        'event_type' => $eventType,
                        'listener' => get_class($listener),
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $event['results'] = $results;
            $event['status'] = 'published';
            $this->eventStats['published_events']++;
            
            // Aggiunge l'evento alla cronologia
            $this->eventHistory[] = $event;
            
            Log::info('Event published successfully', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'listener_count' => count($results),
                'successful_listeners' => count(array_filter($results, fn($r) => $r['success'])),
                'failed_listeners' => count(array_filter($results, fn($r) => !$r['success']))
            ]);
            
            return $event;
            
        } catch (Exception $e) {
            $this->failedEvents++;
            $this->eventStats['failed_events']++;
            
            $event['status'] = 'failed';
            $event['error'] = $e->getMessage();
            $this->eventHistory[] = $event;
            
            Log::error('Failed to publish event', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Pubblica un evento asincrono
     */
    public function publishAsync(string $eventType, array $data, array $metadata = []): array
    {
        $eventId = 'event_async_' . uniqid();
        $event = [
            'id' => $eventId,
            'type' => $eventType,
            'data' => $data,
            'metadata' => array_merge([
                'published_at' => now()->toISOString(),
                'event_bus' => $this->id,
                'async' => true,
                'status' => 'queued'
            ], $metadata)
        ];
        
        // Simula l'aggiunta alla coda
        $this->eventHistory[] = $event;
        
        Log::info('Event queued for async processing', [
            'event_id' => $eventId,
            'event_type' => $eventType,
            'event_bus' => $this->id
        ]);
        
        return $event;
    }

    /**
     * Pubblica un evento di compensazione
     */
    public function publishCompensation(string $originalEventType, array $originalData, string $reason = 'Saga step failed'): array
    {
        $compensationEventType = $this->getCompensationEventType($originalEventType);
        
        $event = $this->publish($compensationEventType, $originalData, [
            'compensation_for' => $originalEventType,
            'reason' => $reason,
            'compensation_at' => now()->toISOString()
        ]);
        
        Log::info('Compensation event published', [
            'original_event_type' => $originalEventType,
            'compensation_event_type' => $compensationEventType,
            'reason' => $reason,
            'event_bus' => $this->id
        ]);
        
        return $event;
    }

    /**
     * Ottiene il tipo di evento di compensazione
     */
    private function getCompensationEventType(string $originalEventType): string
    {
        $compensationMap = [
            'UserValidated' => 'UserValidationFailed',
            'InventoryReserved' => 'InventoryReleaseRequested',
            'OrderCreated' => 'OrderCancellationRequested',
            'PaymentProcessed' => 'PaymentRefundRequested',
            'NotificationSent' => 'NotificationCancellationRequested',
            'OrderCancelled' => 'OrderRestorationRequested',
            'PaymentRefunded' => 'PaymentRechargeRequested',
            'InventoryReleased' => 'InventoryReservationRequested',
            'NotificationCancelled' => 'NotificationResendRequested'
        ];
        
        return $compensationMap[$originalEventType] ?? 'UnknownCompensation';
    }

    /**
     * Riprova un evento fallito
     */
    public function retryEvent(string $eventId, int $maxRetries = 3): array
    {
        $event = $this->findEventById($eventId);
        if (!$event) {
            throw new Exception("Event {$eventId} not found");
        }
        
        if ($event['metadata']['retry_count'] >= $maxRetries) {
            throw new Exception("Event {$eventId} has reached maximum retry attempts");
        }
        
        $event['metadata']['retry_count']++;
        $event['metadata']['last_retry_at'] = now()->toISOString();
        
        $this->eventStats['retry_events']++;
        
        Log::info('Retrying event', [
            'event_id' => $eventId,
            'retry_count' => $event['metadata']['retry_count'],
            'max_retries' => $maxRetries
        ]);
        
        // Riprova l'evento
        return $this->publish($event['type'], $event['data'], $event['metadata']);
    }

    /**
     * Trova un evento per ID
     */
    private function findEventById(string $eventId): ?array
    {
        foreach ($this->eventHistory as $event) {
            if ($event['id'] === $eventId) {
                return $event;
            }
        }
        return null;
    }

    /**
     * Ottiene gli eventi per tipo
     */
    public function getEventsByType(string $eventType): array
    {
        return array_filter($this->eventHistory, fn($event) => $event['type'] === $eventType);
    }

    /**
     * Ottiene gli eventi per utente
     */
    public function getEventsByUser(int $userId): array
    {
        return array_filter($this->eventHistory, function($event) use ($userId) {
            return isset($event['data']['user_id']) && $event['data']['user_id'] === $userId;
        });
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'service' => 'EventBusService',
            'total_events' => $this->totalEvents,
            'failed_events' => $this->failedEvents,
            'success_rate' => $this->totalEvents > 0 
                ? round((($this->totalEvents - $this->failedEvents) / $this->totalEvents) * 100, 2)
                : 100,
            'event_stats' => $this->eventStats,
            'subscriber_count' => array_sum(array_map('count', $this->subscribers)),
            'event_types' => array_keys($this->subscribers),
            'event_history_count' => count($this->eventHistory)
        ];
    }

    /**
     * Ottiene la cronologia degli eventi
     */
    public function getEventHistory(int $limit = 100): array
    {
        return array_slice(array_reverse($this->eventHistory), 0, $limit);
    }

    /**
     * Pulisce la cronologia degli eventi
     */
    public function cleanupEventHistory(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        $deletedCount = 0;
        
        $this->eventHistory = array_filter($this->eventHistory, function($event) use ($cutoffDate, &$deletedCount) {
            $eventDate = now()->parse($event['metadata']['published_at']);
            if ($eventDate->isBefore($cutoffDate)) {
                $deletedCount++;
                return false;
            }
            return true;
        });
        
        Log::info('Event history cleaned up', [
            'deleted_count' => $deletedCount,
            'remaining_count' => count($this->eventHistory),
            'event_bus' => $this->id
        ]);
        
        return $deletedCount;
    }

    /**
     * Verifica se un evento è duplicato
     */
    public function isDuplicateEvent(string $eventType, array $data): bool
    {
        $recentEvents = array_slice($this->eventHistory, -100); // Ultimi 100 eventi
        
        foreach ($recentEvents as $event) {
            if ($event['type'] === $eventType && 
                $event['data'] === $data && 
                now()->parse($event['metadata']['published_at'])->isAfter(now()->subMinutes(5))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Pulisce le risorse
     */
    public function cleanup(): void
    {
        $this->subscribers = [];
        $this->eventHistory = [];
        $this->eventStats = [
            'total_events' => 0,
            'published_events' => 0,
            'failed_events' => 0,
            'duplicate_events' => 0,
            'retry_events' => 0
        ];
        
        Log::info('EventBusService cleanup completed', ['id' => $this->id]);
    }
}
