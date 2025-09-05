<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OutboxEvent;
use App\Services\OutboxService;
use App\Services\EventPublisherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OutboxController extends Controller
{
    public function __construct(
        private OutboxService $outboxService,
        private EventPublisherService $eventPublisher
    ) {}

    /**
     * Mostra l'interfaccia principale
     */
    public function index(): View
    {
        $stats = $this->outboxService->getOutboxStats();
        $recentEvents = OutboxEvent::with('aggregate')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('outbox.example', compact('stats', 'recentEvents'));
    }

    /**
     * Crea un nuovo ordine con evento outbox
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $order = $this->outboxService->createOrderWithEvent([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'amount' => $request->amount,
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo',
                'order' => $order,
                'outbox_event' => $order->getLastOutboxEvent()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggiorna un ordine esistente
     */
    public function updateOrder(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'amount' => 'sometimes|numeric|min:0.01',
            'status' => 'sometimes|in:pending,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $order = $this->outboxService->updateOrderWithEvent(
                $orderId, 
                $request->only(['customer_name', 'customer_email', 'amount', 'status', 'notes'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine aggiornato con successo',
                'order' => $order,
                'outbox_event' => $order->getLastOutboxEvent()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancella un ordine
     */
    public function deleteOrder(int $orderId): JsonResponse
    {
        try {
            $this->outboxService->deleteOrderWithEvent($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Ordine cancellato con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella cancellazione dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene lo stato dell'outbox
     */
    public function getStatus(): JsonResponse
    {
        $stats = $this->outboxService->getOutboxStats();
        $recentEvents = OutboxEvent::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
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
     * Ottiene eventi per un ordine specifico
     */
    public function getOrderEvents(int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $events = $order->getOutboxEvents();

        return response()->json([
            'order_id' => $orderId,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'status' => $event->status,
                    'retry_count' => $event->retry_count,
                    'created_at' => $event->created_at->toISOString(),
                    'scheduled_at' => $event->scheduled_at?->toISOString(),
                    'published_at' => $event->published_at?->toISOString(),
                    'error_message' => $event->error_message
                ];
            })
        ]);
    }

    /**
     * Processa manualmente gli eventi outbox
     */
    public function processEvents(): JsonResponse
    {
        try {
            // Dispatcha il job per processare gli eventi
            \App\Jobs\ProcessOutboxEventsJob::dispatch();

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
     * Pulisce eventi pubblicati vecchi
     */
    public function cleanupEvents(Request $request): JsonResponse
    {
        $daysOld = $request->get('days_old', 7);
        
        try {
            $deletedCount = $this->outboxService->cleanupPublishedEvents($daysOld);

            return response()->json([
                'success' => true,
                'message' => "Rimossi {$deletedCount} eventi pubblicati più vecchi di {$daysOld} giorni"
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
            $isConnected = $this->eventPublisher->testConnection();

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
        $stats = $this->outboxService->getOutboxStats();
        $eventTypeStats = OutboxEvent::getStatsByEventType();
        
        $orders = Order::withCount('outboxEvents')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'outbox_stats' => $stats,
            'event_type_stats' => $eventTypeStats,
            'recent_orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'amount' => $order->amount,
                    'status' => $order->status,
                    'events_count' => $order->outbox_events_count,
                    'created_at' => $order->created_at->toISOString()
                ];
            })
        ]);
    }
}
