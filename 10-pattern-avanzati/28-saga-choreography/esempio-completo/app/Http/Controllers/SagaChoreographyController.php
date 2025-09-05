<?php

namespace App\Http\Controllers;

use App\Services\EventBusService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Events\UserValidated;
use App\Events\OrderCancellationRequested;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Controller per il Saga Choreography Pattern
 * 
 * Questo controller gestisce le operazioni delle saga e fornisce
 * un'interfaccia per monitorare e controllare le transazioni distribuite.
 */
class SagaChoreographyController extends Controller
{
    private EventBusService $eventBus;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;
    private InventoryService $inventoryService;
    private NotificationService $notificationService;

    public function __construct(
        EventBusService $eventBus,
        UserService $userService,
        ProductService $productService,
        OrderService $orderService,
        PaymentService $paymentService,
        InventoryService $inventoryService,
        NotificationService $notificationService
    ) {
        $this->eventBus = $eventBus;
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
        
        // Registra i listener per gli eventi
        $this->registerEventListeners();
    }

    /**
     * Mostra la pagina di esempio del Saga Choreography Pattern
     */
    public function example()
    {
        return view('saga-choreography.example', [
            'eventBus' => $this->eventBus,
            'userService' => $this->userService,
            'productService' => $this->productService,
            'orderService' => $this->orderService,
            'paymentService' => $this->paymentService,
            'inventoryService' => $this->inventoryService,
            'notificationService' => $this->notificationService
        ]);
    }

    /**
     * Avvia una saga per creare un ordine
     */
    public function startCreateOrderSaga(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'total' => 'required|numeric|min:0'
            ]);

            // Pubblica l'evento UserValidated per avviare la saga
            $event = new UserValidated(
                $data['user_id'],
                'Test User',
                'test@example.com',
                array_merge($data, [
                    'saga_id' => uniqid('saga_'),
                    'started_at' => now()->toISOString()
                ])
            );
            
            event($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Create order saga started successfully',
                'data' => [
                    'saga_id' => $event->metadata['saga_id'],
                    'event_id' => $event->eventId,
                    'user_id' => $data['user_id'],
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'total' => $data['total']
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start create order saga',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Avvia una saga per cancellare un ordine
     */
    public function startCancelOrderSaga(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'order_id' => 'required|integer',
                'user_id' => 'required|integer'
            ]);

            // Pubblica l'evento OrderCancellationRequested per avviare la saga
            $event = new OrderCancellationRequested(
                $data['order_id'],
                $data['user_id'],
                'Order cancellation requested',
                array_merge($data, [
                    'saga_id' => uniqid('saga_'),
                    'started_at' => now()->toISOString()
                ])
            );
            
            event($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Cancel order saga started successfully',
                'data' => [
                    'saga_id' => $event->metadata['saga_id'],
                    'event_id' => $event->eventId,
                    'order_id' => $data['order_id'],
                    'user_id' => $data['user_id']
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start cancel order saga',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pubblica un evento personalizzato
     */
    public function publishEvent(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'event_type' => 'required|string',
                'event_data' => 'required|array',
                'metadata' => 'array'
            ]);

            $event = $this->eventBus->publish(
                $data['event_type'],
                $data['event_data'],
                $data['metadata'] ?? []
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Event published successfully',
                'data' => $event
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del sistema
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'event_bus' => $this->eventBus->getStats(),
                'user_service' => $this->userService->getStats(),
                'product_service' => $this->productService->getStats(),
                'order_service' => $this->orderService->getStats(),
                'payment_service' => $this->paymentService->getStats(),
                'inventory_service' => $this->inventoryService->getStats(),
                'notification_service' => $this->notificationService->getStats()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene la cronologia degli eventi
     */
    public function getEventHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 100);
            $history = $this->eventBus->getEventHistory($limit);
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get event history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene gli eventi per tipo
     */
    public function getEventsByType(Request $request): JsonResponse
    {
        try {
            $eventType = $request->input('event_type');
            if (!$eventType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event type is required'
                ], 400);
            }

            $events = $this->eventBus->getEventsByType($eventType);
            
            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get events by type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene gli eventi per utente
     */
    public function getEventsByUser(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            $events = $this->eventBus->getEventsByUser($userId);
            
            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get events by user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa la resilienza del sistema
     */
    public function testResilience(): JsonResponse
    {
        try {
            $results = [];
            $startTime = microtime(true);
            
            // Simula multiple saga simultanee
            for ($i = 0; $i < 5; $i++) {
                try {
                    $event = new UserValidated(
                        $i + 1,
                        "Test User $i",
                        "test$i@example.com",
                        [
                            'product_id' => $i + 1,
                            'quantity' => 1,
                            'total' => 29.99,
                            'saga_id' => uniqid('saga_')
                        ]
                    );
                    
                    event($event);
                    $results[] = ['event_id' => $event->eventId, 'status' => 'published'];
                } catch (Exception $e) {
                    $results[] = ['error' => $e->getMessage()];
                }
            }
            
            $duration = microtime(true) - $startTime;
            
            return response()->json([
                'success' => true,
                'message' => 'Resilience test completed',
                'data' => [
                    'duration' => $duration,
                    'events' => $results,
                    'total_events' => count($results),
                    'successful_events' => count(array_filter($results, fn($r) => isset($r['event_id']))),
                    'failed_events' => count(array_filter($results, fn($r) => isset($r['error'])))
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test resilience',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce gli eventi vecchi
     */
    public function cleanupOldEvents(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $deletedCount = $this->eventBus->cleanupEventHistory($days);
            
            return response()->json([
                'success' => true,
                'message' => 'Old events cleaned up successfully',
                'data' => [
                    'deleted_count' => $deletedCount,
                    'days' => $days
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra i listener per gli eventi
     */
    private function registerEventListeners(): void
    {
        // Listener per UserValidated
        $this->eventBus->subscribe('UserValidated', function($event) {
            $this->handleUserValidated($event);
        });

        // Listener per InventoryReserved
        $this->eventBus->subscribe('InventoryReserved', function($event) {
            $this->handleInventoryReserved($event);
        });

        // Listener per OrderCreated
        $this->eventBus->subscribe('OrderCreated', function($event) {
            $this->handleOrderCreated($event);
        });

        // Listener per PaymentProcessed
        $this->eventBus->subscribe('PaymentProcessed', function($event) {
            $this->handlePaymentProcessed($event);
        });

        // Listener per NotificationSent
        $this->eventBus->subscribe('NotificationSent', function($event) {
            $this->handleNotificationSent($event);
        });
    }

    /**
     * Gestisce l'evento UserValidated
     */
    private function handleUserValidated($event): void
    {
        try {
            // Simula la riserva dell'inventario
            $reservation = $this->inventoryService->reserveInventory(
                $event['data']['product_id'] ?? 1,
                $event['data']['quantity'] ?? 1
            );
            
            // Pubblica l'evento InventoryReserved
            $this->eventBus->publish('InventoryReserved', $reservation, $event['metadata']);
            
        } catch (Exception $e) {
            // Pubblica l'evento di compensazione
            $this->eventBus->publishCompensation('UserValidated', $event['data'], $e->getMessage());
        }
    }

    /**
     * Gestisce l'evento InventoryReserved
     */
    private function handleInventoryReserved($event): void
    {
        try {
            // Simula la creazione dell'ordine
            $order = $this->orderService->createOrder([
                'user_id' => $event['data']['user_id'] ?? 1,
                'total' => $event['data']['total'] ?? 29.99,
                'items' => [
                    [
                        'product_id' => $event['data']['product_id'] ?? 1,
                        'quantity' => $event['data']['quantity'] ?? 1,
                        'price' => $event['data']['price'] ?? 29.99
                    ]
                ]
            ]);
            
            // Pubblica l'evento OrderCreated
            $this->eventBus->publish('OrderCreated', $order, $event['metadata']);
            
        } catch (Exception $e) {
            // Pubblica l'evento di compensazione
            $this->eventBus->publishCompensation('InventoryReserved', $event['data'], $e->getMessage());
        }
    }

    /**
     * Gestisce l'evento OrderCreated
     */
    private function handleOrderCreated($event): void
    {
        try {
            // Simula il processamento del pagamento
            $payment = $this->paymentService->processPayment([
                'order_id' => $event['data']['id'],
                'user_id' => $event['data']['user_id'],
                'amount' => $event['data']['total']
            ]);
            
            // Pubblica l'evento PaymentProcessed
            $this->eventBus->publish('PaymentProcessed', $payment, $event['metadata']);
            
        } catch (Exception $e) {
            // Pubblica l'evento di compensazione
            $this->eventBus->publishCompensation('OrderCreated', $event['data'], $e->getMessage());
        }
    }

    /**
     * Gestisce l'evento PaymentProcessed
     */
    private function handlePaymentProcessed($event): void
    {
        try {
            // Simula l'invio della notifica
            $notification = $this->notificationService->sendNotification(
                $event['data']['user_id'],
                'Your order has been successfully created and processed.'
            );
            
            // Pubblica l'evento NotificationSent
            $this->eventBus->publish('NotificationSent', $notification, $event['metadata']);
            
        } catch (Exception $e) {
            // Pubblica l'evento di compensazione
            $this->eventBus->publishCompensation('PaymentProcessed', $event['data'], $e->getMessage());
        }
    }

    /**
     * Gestisce l'evento NotificationSent
     */
    private function handleNotificationSent($event): void
    {
        // La saga è completata
        Log::info('Saga completed successfully', [
            'event_id' => $event['id'],
            'saga_id' => $event['metadata']['saga_id'] ?? 'unknown'
        ]);
    }
}
