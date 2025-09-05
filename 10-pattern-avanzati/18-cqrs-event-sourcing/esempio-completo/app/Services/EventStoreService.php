<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

class EventStoreService
{
    protected $tableName = 'events';

    /**
     * Aggiunge un evento all'Event Store
     */
    public function appendEvent(string $aggregateId, $event)
    {
        try {
            // Converte l'evento in array
            $eventData = $event->toArray();
            
            // Aggiunge metadati
            $eventData['aggregate_id'] = $aggregateId;
            $eventData['created_at'] = now();
            $eventData['updated_at'] = now();
            
            // Salva l'evento nel database
            DB::table($this->tableName)->insert($eventData);
            
            // Dispatches l'evento per le projection
            Event::dispatch($event);
            
            Log::info("Event Store: Evento salvato", [
                'aggregate_id' => $aggregateId,
                'event_type' => $eventData['event_type'],
                'event_id' => $eventData['event_id']
            ]);
            
            return $eventData['event_id'];
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel salvataggio dell'evento", [
                'aggregate_id' => $aggregateId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene tutti gli eventi per un aggregate
     */
    public function getEvents(string $aggregateId)
    {
        try {
            $events = DB::table($this->tableName)
                ->where('aggregate_id', $aggregateId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
            
            // Converte in array associativi
            return array_map(function($event) {
                return (array) $event;
            }, $events);
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel recupero degli eventi", [
                'aggregate_id' => $aggregateId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ottiene eventi per tipo
     */
    public function getEventsByType(string $eventType)
    {
        try {
            $events = DB::table($this->tableName)
                ->where('event_type', $eventType)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            return array_map(function($event) {
                return (array) $event;
            }, $events);
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel recupero degli eventi per tipo", [
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verifica se un aggregate esiste
     */
    public function aggregateExists(string $aggregateId)
    {
        try {
            $count = DB::table($this->tableName)
                ->where('aggregate_id', $aggregateId)
                ->count();
            
            return $count > 0;
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nella verifica dell'aggregate", [
                'aggregate_id' => $aggregateId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ottiene l'ultimo evento per un aggregate
     */
    public function getLastEvent(string $aggregateId)
    {
        try {
            $event = DB::table($this->tableName)
                ->where('aggregate_id', $aggregateId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            return $event ? (array) $event : null;
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel recupero dell'ultimo evento", [
                'aggregate_id' => $aggregateId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Ottiene eventi in un range di date
     */
    public function getEventsInDateRange($startDate, $endDate)
    {
        try {
            $events = DB::table($this->tableName)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
            
            return array_map(function($event) {
                return (array) $event;
            }, $events);
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel recupero degli eventi per data", [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Ottiene statistiche dell'Event Store
     */
    public function getStats()
    {
        try {
            $totalEvents = DB::table($this->tableName)->count();
            
            $eventsByType = DB::table($this->tableName)
                ->select('event_type', DB::raw('count(*) as count'))
                ->groupBy('event_type')
                ->get()
                ->pluck('count', 'event_type')
                ->toArray();
            
            $uniqueAggregates = DB::table($this->tableName)
                ->distinct('aggregate_id')
                ->count('aggregate_id');
            
            $oldestEvent = DB::table($this->tableName)
                ->orderBy('created_at', 'asc')
                ->first();
            
            $newestEvent = DB::table($this->tableName)
                ->orderBy('created_at', 'desc')
                ->first();
            
            return [
                'total_events' => $totalEvents,
                'unique_aggregates' => $uniqueAggregates,
                'events_by_type' => $eventsByType,
                'oldest_event' => $oldestEvent ? $oldestEvent->created_at : null,
                'newest_event' => $newestEvent ? $newestEvent->created_at : null
            ];
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel recupero delle statistiche", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Replay degli eventi per un aggregate
     */
    public function replayEvents(string $aggregateId)
    {
        try {
            $events = $this->getEvents($aggregateId);
            
            Log::info("Event Store: Replay eventi per aggregate", [
                'aggregate_id' => $aggregateId,
                'events_count' => count($events)
            ]);
            
            // Dispatches tutti gli eventi per ricostruire le projection
            foreach ($events as $eventData) {
                $this->dispatchEventFromData($eventData);
            }
            
            return [
                'success' => true,
                'events_replayed' => count($events)
            ];
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel replay degli eventi", [
                'aggregate_id' => $aggregateId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Dispatches un evento dai dati salvati
     */
    private function dispatchEventFromData(array $eventData)
    {
        try {
            $eventType = $eventData['event_type'];
            
            switch ($eventType) {
                case 'OrderCreated':
                    $event = new \App\Events\OrderCreated($eventData['data']);
                    break;
                case 'OrderUpdated':
                    $event = new \App\Events\OrderUpdated($eventData['data']);
                    break;
                case 'OrderCancelled':
                    $event = new \App\Events\OrderCancelled($eventData['data']);
                    break;
                default:
                    Log::warning("Event Store: Tipo evento sconosciuto", [
                        'event_type' => $eventType
                    ]);
                    return;
            }
            
            Event::dispatch($event);
            
        } catch (\Exception $e) {
            Log::error("Event Store: Errore nel dispatch dell'evento", [
                'event_data' => $eventData,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test del pattern CQRS + Event Sourcing
     */
    public function testCqrsEventSourcing()
    {
        $results = [];
        
        try {
            // Test 1: Creazione evento
            $testEvent = new \App\Events\OrderCreated([
                'order_id' => 'test_order_' . time(),
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'items' => [],
                'total_amount' => 100.00,
                'status' => 'pending'
            ]);
            
            $eventId = $this->appendEvent('test_aggregate', $testEvent);
            $results['event_creation'] = $eventId ? 'success' : 'failed';
            
            // Test 2: Recupero eventi
            $events = $this->getEvents('test_aggregate');
            $results['event_retrieval'] = count($events) > 0 ? 'success' : 'failed';
            
            // Test 3: Verifica esistenza aggregate
            $exists = $this->aggregateExists('test_aggregate');
            $results['aggregate_exists'] = $exists ? 'success' : 'failed';
            
            // Test 4: Statistiche
            $stats = $this->getStats();
            $results['stats'] = $stats;
            
            // Test 5: Replay
            $replay = $this->replayEvents('test_aggregate');
            $results['replay'] = $replay['success'] ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
