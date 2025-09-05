<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\InboxEvent;
use App\Services\InboxService;
use App\Services\EventConsumerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function __construct(
        private InboxService $inboxService,
        private EventConsumerService $eventConsumer
    ) {}

    /**
     * Mostra l'interfaccia principale
     */
    public function index(): View
    {
        $stats = $this->inboxService->getInboxStats();
        $recentEvents = InboxEvent::orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('inbox.example', compact('stats', 'recentEvents'));
    }

    /**
     * Riceve un evento nell'inbox
     */
    public function receiveEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|string|max:255',
            'event_type' => 'required|string|max:255',
            'event_data' => 'required|array'
        ]);

        try {
            $inboxEvent = $this->inboxService->receiveEvent(
                $request->event_id,
                $request->event_type,
                $request->event_data
            );

            return response()->json([
                'success' => true,
                'message' => 'Evento ricevuto nell\'inbox',
                'inbox_event' => $inboxEvent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella ricezione dell\'evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula l'arrivo di un evento
     */
    public function simulateEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|in:OrderCreated,OrderUpdated,OrderDeleted,PaymentProcessed,InventoryUpdated',
            'order_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0'
        ]);

        try {
            $eventId = 'sim-' . time() . '-' . rand(1000, 9999);
            $eventData = $this->buildEventData($request->all());

            $inboxEvent = $this->inboxService->receiveEvent(
                $eventId,
                $request->event_type,
                $eventData
            );

            return response()->json([
                'success' => true,
                'message' => 'Evento simulato ricevuto',
                'event_id' => $eventId,
                'inbox_event' => $inboxEvent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella simulazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene lo stato dell'inbox
     */
    public function getStatus(): JsonResponse
    {
        $stats = $this->inboxService->getInboxStats();
        $recentEvents = InboxEvent::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'status' => $event->status,
                    'retry_count' => $event->retry_count,
                    'created_at' => $event->created_at->toISOString(),
                    'scheduled_at' => $event->scheduled_at?->toISOString()
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_events' => $recentEvents,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Ottiene eventi per tipo
     */
    public function getEventsByType(string $eventType): JsonResponse
    {
        $events = $this->inboxService->getEventsByType($eventType);

        return response()->json([
            'event_type' => $eventType,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_id' => $event->event_id,
                    'status' => $event->status,
                    'retry_count' => $event->retry_count,
                    'created_at' => $event->created_at->toISOString(),
                    'scheduled_at' => $event->scheduled_at?->toISOString(),
                    'processed_at' => $event->processed_at?->toISOString(),
                    'error_message' => $event->error_message
                ];
            })
        ]);
    }

    /**
     * Processa manualmente gli eventi inbox
     */
    public function processEvents(): JsonResponse
    {
        try {
            // Dispatcha il job per processare gli eventi
            \App\Jobs\ProcessInboxEventsJob::dispatch();

            return response()->json([
                'success' => true,
                'message' => 'Job di processing eventi avviato'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'avvio del processing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce eventi processati vecchi
     */
    public function cleanupEvents(Request $request): JsonResponse
    {
        $daysOld = $request->get('days_old', 7);
        
        try {
            $deletedCount = $this->inboxService->cleanupProcessedEvents($daysOld);

            return response()->json([
                'success' => true,
                'message' => "Rimossi {$deletedCount} eventi processati più vecchi di {$daysOld} giorni"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella pulizia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa la connessione al sistema di messaggistica
     */
    public function testConnection(): JsonResponse
    {
        try {
            $isConnected = $this->eventConsumer->testConnection();

            return response()->json([
                'success' => $isConnected,
                'message' => $isConnected ? 'Connessione OK' : 'Connessione fallita'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel test di connessione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche dettagliate
     */
    public function getDetailedStats(): JsonResponse
    {
        $stats = $this->inboxService->getInboxStats();
        $eventTypeStats = InboxEvent::getStatsByEventType();
        
        $orders = Order::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'inbox_stats' => $stats,
            'event_type_stats' => $eventTypeStats,
            'recent_orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'amount' => $order->amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString()
                ];
            }),
            'average_processing_time' => InboxEvent::getAverageProcessingTime(),
            'success_rate' => InboxEvent::getSuccessRate()
        ]);
    }

    /**
     * Ottiene eventi duplicati
     */
    public function getDuplicateEvents(): JsonResponse
    {
        $duplicates = InboxEvent::getDuplicateEvents();

        return response()->json([
            'duplicate_events' => $duplicates->map(function ($event) {
                return [
                    'event_id' => $event->event_id,
                    'count' => InboxEvent::where('event_id', $event->event_id)->count()
                ];
            })
        ]);
    }

    /**
     * Ripristina eventi stuck
     */
    public function restoreStuckEvents(): JsonResponse
    {
        try {
            $restoredCount = $this->inboxService->restoreStuckEvents();

            return response()->json([
                'success' => true,
                'message' => "Ripristinati {$restoredCount} eventi stuck"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel ripristino: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Costruisce i dati dell'evento basati sulla richiesta
     */
    private function buildEventData(array $requestData): array
    {
        $eventData = [];

        if (isset($requestData['order_id'])) {
            $eventData['order_id'] = $requestData['order_id'];
        }

        if (isset($requestData['customer_name'])) {
            $eventData['customer_name'] = $requestData['customer_name'];
        }

        if (isset($requestData['amount'])) {
            $eventData['amount'] = $requestData['amount'];
        }

        // Aggiungi dati specifici per tipo di evento
        switch ($requestData['event_type']) {
            case 'OrderCreated':
                $eventData = array_merge($eventData, [
                    'customer_name' => $requestData['customer_name'] ?? 'Mario Rossi',
                    'customer_email' => 'mario.rossi@example.com',
                    'amount' => $requestData['amount'] ?? 100.50,
                    'status' => 'pending'
                ]);
                break;

            case 'OrderUpdated':
                $eventData = array_merge($eventData, [
                    'status' => 'processing',
                    'updated_fields' => ['status']
                ]);
                break;

            case 'OrderDeleted':
                $eventData = array_merge($eventData, [
                    'deleted_at' => now()->toISOString()
                ]);
                break;

            case 'PaymentProcessed':
                $eventData = array_merge($eventData, [
                    'payment_id' => 'pay-' . time(),
                    'payment_method' => 'credit_card',
                    'amount' => $requestData['amount'] ?? 100.50
                ]);
                break;

            case 'InventoryUpdated':
                $eventData = array_merge($eventData, [
                    'product_id' => rand(1, 100),
                    'quantity' => rand(1, 50),
                    'operation' => 'decrease'
                ]);
                break;
        }

        return $eventData;
    }
}
