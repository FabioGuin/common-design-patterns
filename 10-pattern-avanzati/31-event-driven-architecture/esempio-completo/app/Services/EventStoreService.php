<?php

namespace App\Services;

use App\Models\EventStore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EventStoreService
{
    /**
     * Salva un evento nell'event store
     */
    public function store(string $eventType, array $eventData, ?int $aggregateId = null): EventStore
    {
        try {
            $event = EventStore::create([
                'event_type' => $eventType,
                'event_data' => $eventData,
                'aggregate_id' => $aggregateId,
                'event_id' => $this->generateEventId(),
                'version' => $this->getNextVersion($aggregateId),
                'occurred_at' => now()
            ]);

            Log::info('Event stored in event store', [
                'event_id' => $event->event_id,
                'event_type' => $eventType,
                'aggregate_id' => $aggregateId
            ]);

            return $event;

        } catch (\Exception $e) {
            Log::error('Failed to store event', [
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene eventi per un aggregato
     */
    public function getEventsForAggregate(int $aggregateId): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::where('aggregate_id', $aggregateId)
            ->orderBy('version')
            ->get();
    }

    /**
     * Ottiene eventi per tipo
     */
    public function getEventsByType(string $eventType): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::where('event_type', $eventType)
            ->orderBy('occurred_at')
            ->get();
    }

    /**
     * Ottiene eventi in un range di date
     */
    public function getEventsByDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::whereBetween('occurred_at', [$startDate, $endDate])
            ->orderBy('occurred_at')
            ->get();
    }

    /**
     * Ottiene eventi recenti
     */
    public function getRecentEvents(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Ottiene eventi per replay
     */
    public function getEventsForReplay(\Carbon\Carbon $fromDate, ?\Carbon\Carbon $toDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = EventStore::where('occurred_at', '>=', $fromDate);
        
        if ($toDate) {
            $query->where('occurred_at', '<=', $toDate);
        }
        
        return $query->orderBy('occurred_at')
            ->get();
    }

    /**
     * Ottiene l'ultimo evento per un aggregato
     */
    public function getLastEventForAggregate(int $aggregateId): ?EventStore
    {
        return EventStore::where('aggregate_id', $aggregateId)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Ottiene la versione corrente di un aggregato
     */
    public function getCurrentVersion(int $aggregateId): int
    {
        $lastEvent = $this->getLastEventForAggregate($aggregateId);
        return $lastEvent ? $lastEvent->version : 0;
    }

    /**
     * Ottiene statistiche dell'event store
     */
    public function getStats(): array
    {
        $totalEvents = EventStore::count();
        $eventsByType = EventStore::selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        $eventsByDate = EventStore::selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'total_events' => $totalEvents,
            'events_by_type' => $eventsByType,
            'events_by_date' => $eventsByDate,
            'oldest_event' => EventStore::min('occurred_at'),
            'newest_event' => EventStore::max('occurred_at')
        ];
    }

    /**
     * Pulisce eventi vecchi
     */
    public function cleanupOldEvents(int $daysOld = 365): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        $deletedCount = EventStore::where('occurred_at', '<', $cutoffDate)
            ->delete();

        Log::info('Cleaned up old events', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate
        ]);

        return $deletedCount;
    }

    /**
     * Replay di eventi per un aggregato
     */
    public function replayEventsForAggregate(int $aggregateId, callable $handler): void
    {
        $events = $this->getEventsForAggregate($aggregateId);

        foreach ($events as $event) {
            try {
                $handler($event);
            } catch (\Exception $e) {
                Log::error('Failed to replay event', [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Replay di eventi per tipo
     */
    public function replayEventsByType(string $eventType, callable $handler): void
    {
        $events = $this->getEventsByType($eventType);

        foreach ($events as $event) {
            try {
                $handler($event);
            } catch (\Exception $e) {
                Log::error('Failed to replay event by type', [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Replay di eventi in un range di date
     */
    public function replayEventsByDateRange(\Carbon\Carbon $fromDate, \Carbon\Carbon $toDate, callable $handler): void
    {
        $events = $this->getEventsForReplay($fromDate, $toDate);

        foreach ($events as $event) {
            try {
                $handler($event);
            } catch (\Exception $e) {
                Log::error('Failed to replay event by date range', [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Ottiene eventi per un pattern di ricerca
     */
    public function searchEvents(string $pattern): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::where('event_type', 'LIKE', "%{$pattern}%")
            ->orWhere('event_data', 'LIKE', "%{$pattern}%")
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi per un aggregato con versione specifica
     */
    public function getEventsForAggregateFromVersion(int $aggregateId, int $fromVersion): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get();
    }

    /**
     * Genera un ID univoco per l'evento
     */
    private function generateEventId(): string
    {
        return 'evt_' . time() . '_' . uniqid();
    }

    /**
     * Ottiene la prossima versione per un aggregato
     */
    private function getNextVersion(?int $aggregateId): int
    {
        if (!$aggregateId) {
            return 1;
        }

        return $this->getCurrentVersion($aggregateId) + 1;
    }

    /**
     * Ottiene eventi duplicati
     */
    public function getDuplicateEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::select('event_id')
            ->groupBy('event_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    /**
     * Verifica se un evento esiste
     */
    public function eventExists(string $eventId): bool
    {
        return EventStore::where('event_id', $eventId)->exists();
    }

    /**
     * Ottiene eventi per un utente specifico
     */
    public function getEventsForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return EventStore::whereJsonContains('event_data->user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }
}
