<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\EventStore;
use App\Services\EventBusService;
use App\Services\EventStoreService;
use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Events\PaymentProcessed;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EventDrivenController extends Controller
{
    public function __construct(
        private EventBusService $eventBus,
        private EventStoreService $eventStore
    ) {}

    /**
     * Mostra l'interfaccia principale
     */
    public function index(): View
    {
        $stats = $this->eventStore->getStats();
        $recentEvents = $this->eventStore->getRecentEvents(20);
        $orders = Order::orderBy('created_at', 'desc')->limit(10)->get();

        return view('event-driven.example', compact('stats', 'recentEvents', 'orders'));
    }

    /**
     * Crea un nuovo ordine e pubblica l'evento
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
            // Crea l'ordine
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'amount' => $request->amount,
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            // Prepara i dati per l'evento
            $orderData = [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'amount' => $order->amount,
                'status' => $order->status,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toISOString()
            ];

            // Pubblica l'evento
            $this->eventBus->publish(OrderCreated::class, $orderData);

            // Salva l'evento nell'event store
            $this->eventStore->store('OrderCreated', $orderData, $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato e evento pubblicato con successo',
                'order' => $order,
                'event_published' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggiorna un ordine e pubblica l'evento
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
            $order = Order::findOrFail($orderId);
            $oldData = $order->toArray();

            // Aggiorna l'ordine
            $order->update($request->only(['customer_name', 'customer_email', 'amount', 'status', 'notes']));

            // Prepara i dati per l'evento
            $orderData = [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'amount' => $order->amount,
                'status' => $order->status,
                'notes' => $order->notes,
                'updated_at' => $order->updated_at->toISOString()
            ];

            // Calcola le modifiche
            $changes = array_diff_assoc($orderData, $oldData);

            // Pubblica l'evento
            $this->eventBus->publish(OrderUpdated::class, $orderData, $changes);

            // Salva l'evento nell'event store
            $this->eventStore->store('OrderUpdated', $orderData, $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Ordine aggiornato e evento pubblicato con successo',
                'order' => $order,
                'changes' => $changes,
                'event_published' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula un pagamento e pubblica l'evento
     */
    public function processPayment(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            $order = Order::findOrFail($orderId);

            // Simula il processing del pagamento
            $paymentData = [
                'payment_id' => 'pay_' . time() . '_' . rand(1000, 9999),
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'processed_at' => now()->toISOString()
            ];

            // Pubblica l'evento
            $this->eventBus->publish(PaymentProcessed::class, $paymentData);

            // Salva l'evento nell'event store
            $this->eventStore->store('PaymentProcessed', $paymentData, $order->id);

            return response()->json([
                'success' => true,
                'message' => 'Pagamento processato e evento pubblicato con successo',
                'payment' => $paymentData,
                'event_published' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel processing del pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene gli eventi recenti
     */
    public function getEvents(): JsonResponse
    {
        $events = $this->eventStore->getRecentEvents(50);

        return response()->json([
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'aggregate_id' => $event->aggregate_id,
                    'version' => $event->version,
                    'occurred_at' => $event->occurred_at->toISOString(),
                    'event_data' => $event->event_data
                ];
            })
        ]);
    }

    /**
     * Ottiene eventi per un ordine specifico
     */
    public function getOrderEvents(int $orderId): JsonResponse
    {
        $events = $this->eventStore->getEventsForAggregate($orderId);

        return response()->json([
            'order_id' => $orderId,
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'version' => $event->version,
                    'occurred_at' => $event->occurred_at->toISOString(),
                    'event_data' => $event->event_data
                ];
            })
        ]);
    }

    /**
     * Ottiene statistiche degli eventi
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->eventStore->getStats();
        $eventBusStats = $this->eventBus->getStats();

        return response()->json([
            'event_store_stats' => $stats,
            'event_bus_stats' => $eventBusStats,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Replay di eventi
     */
    public function replayEvents(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'nullable|date|after:from_date',
            'event_type' => 'nullable|string'
        ]);

        try {
            $fromDate = \Carbon\Carbon::parse($request->from_date);
            $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date) : null;

            if ($request->event_type) {
                $events = $this->eventStore->getEventsByType($request->event_type);
            } else {
                $events = $this->eventStore->getEventsForReplay($fromDate, $toDate);
            }

            $replayedCount = 0;
            foreach ($events as $event) {
                // Simula il replay dell'evento
                $this->eventBus->publish($event->event_type, $event->event_data);
                $replayedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Replay completato per {$replayedCount} eventi",
                'replayed_count' => $replayedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel replay: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa l'event bus
     */
    public function testEventBus(): JsonResponse
    {
        try {
            $isConnected = $this->eventBus->testConnection();

            return response()->json([
                'success' => $isConnected,
                'message' => $isConnected ? 'Event bus OK' : 'Event bus fallito'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce eventi vecchi
     */
    public function cleanupEvents(Request $request): JsonResponse
    {
        $daysOld = $request->get('days_old', 365);
        
        try {
            $deletedCount = $this->eventStore->cleanupOldEvents($daysOld);

            return response()->json([
                'success' => true,
                'message' => "Rimossi {$deletedCount} eventi più vecchi di {$daysOld} giorni"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella pulizia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le sottoscrizioni dell'event bus
     */
    public function getSubscriptions(): JsonResponse
    {
        $subscriptions = $this->eventBus->getSubscriptions();

        return response()->json([
            'subscriptions' => $subscriptions
        ]);
    }
}
