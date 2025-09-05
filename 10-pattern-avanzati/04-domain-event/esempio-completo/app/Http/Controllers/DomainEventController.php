<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Events\OrderConfirmed;
use App\Events\OrderCancelled;
use App\Events\OrderShipped;
use App\Events\PaymentProcessed;
use App\Events\PaymentFailed;
use App\Services\EventBus;
use App\Services\NotificationService;
use App\Services\InventoryService;
use App\Services\BillingService;
use App\Services\OrderService;

/**
 * Controller per dimostrare il Domain Event Pattern
 * 
 * Questo controller mostra come il Domain Event Pattern
 * disaccoppia i servizi attraverso eventi e listener.
 */
class DomainEventController extends Controller
{
    private EventBus $eventBus;
    private NotificationService $notificationService;
    private InventoryService $inventoryService;
    private BillingService $billingService;
    private OrderService $orderService;

    public function __construct(
        EventBus $eventBus,
        NotificationService $notificationService,
        InventoryService $inventoryService,
        BillingService $billingService,
        OrderService $orderService
    ) {
        $this->eventBus = $eventBus;
        $this->notificationService = $notificationService;
        $this->inventoryService = $inventoryService;
        $this->billingService = $billingService;
        $this->orderService = $orderService;
        
        $this->setupEventListeners();
    }

    /**
     * Endpoint principale - mostra l'interfaccia web
     */
    public function index()
    {
        return view('domain_event.example');
    }

    /**
     * Endpoint di test - dimostra il pattern
     */
    public function test(Request $request): JsonResponse
    {
        $testType = $request->input('type', 'all');
        
        $results = [];
        
        switch ($testType) {
            case 'order-confirmed':
                $results = $this->testOrderConfirmed();
                break;
            case 'order-cancelled':
                $results = $this->testOrderCancelled();
                break;
            case 'order-shipped':
                $results = $this->testOrderShipped();
                break;
            case 'payment-processed':
                $results = $this->testPaymentProcessed();
                break;
            case 'payment-failed':
                $results = $this->testPaymentFailed();
                break;
            default:
                $results = $this->testAllScenarios();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Domain Event Pattern test completed',
            'data' => $results
        ]);
    }

    /**
     * Conferma un ordine
     */
    public function confirmOrder(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => 'required|string',
            'customerId' => 'required|string',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array'
        ]);

        try {
            $event = new OrderConfirmed(
                $request->orderId,
                $request->customerId,
                $request->total,
                $request->items,
                $request->shippingAddress ?? '',
                $request->billingAddress ?? ''
            );

            $this->eventBus->publish($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Order confirmed and events published',
                'data' => $event->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => 'required|string',
            'customerId' => 'required|string',
            'total' => 'required|numeric|min:0',
            'reason' => 'string',
            'items' => 'array'
        ]);

        try {
            $event = new OrderCancelled(
                $request->orderId,
                $request->customerId,
                $request->total,
                $request->reason ?? 'Customer request',
                $request->items ?? []
            );

            $this->eventBus->publish($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled and events published',
                'data' => $event->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Spedisce un ordine
     */
    public function shipOrder(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => 'required|string',
            'customerId' => 'required|string',
            'trackingNumber' => 'required|string',
            'carrier' => 'string',
            'shippingAddress' => 'string'
        ]);

        try {
            $event = new OrderShipped(
                $request->orderId,
                $request->customerId,
                $request->trackingNumber,
                $request->carrier ?? 'DHL',
                $request->shippingAddress ?? ''
            );

            $this->eventBus->publish($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Order shipped and events published',
                'data' => $event->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => 'required|string',
            'customerId' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|string',
            'transactionId' => 'required|string',
            'currency' => 'string'
        ]);

        try {
            $event = new PaymentProcessed(
                $request->orderId,
                $request->customerId,
                $request->amount,
                $request->paymentMethod,
                $request->transactionId,
                $request->currency ?? 'EUR'
            );

            $this->eventBus->publish($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed and events published',
                'data' => $event->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Fallisce un pagamento
     */
    public function failPayment(Request $request): JsonResponse
    {
        $request->validate([
            'orderId' => 'required|string',
            'customerId' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|string',
            'reason' => 'string',
            'currency' => 'string'
        ]);

        try {
            $event = new PaymentFailed(
                $request->orderId,
                $request->customerId,
                $request->amount,
                $request->paymentMethod,
                $request->reason ?? 'Insufficient funds',
                $request->currency ?? 'EUR'
            );

            $this->eventBus->publish($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment failed and events published',
                'data' => $event->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Test per OrderConfirmed
     */
    private function testOrderConfirmed(): array
    {
        $event = new OrderConfirmed(
            'order-123',
            'customer-456',
            150.00,
            [
                ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00],
                ['productId' => 'PROD-002', 'quantity' => 1, 'price' => 50.00]
            ],
            'Via Roma 123, Milano',
            'Via Roma 123, Milano'
        );

        $this->eventBus->publish($event);

        return [
            'event' => $event->toArray(),
            'notifications' => $this->notificationService->getSentNotifications(),
            'inventory' => $this->inventoryService->getAllProducts(),
            'invoices' => $this->billingService->getAllInvoices()
        ];
    }

    /**
     * Test per OrderCancelled
     */
    private function testOrderCancelled(): array
    {
        $event = new OrderCancelled(
            'order-123',
            'customer-456',
            150.00,
            'Customer request',
            [
                ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00],
                ['productId' => 'PROD-002', 'quantity' => 1, 'price' => 50.00]
            ]
        );

        $this->eventBus->publish($event);

        return [
            'event' => $event->toArray(),
            'notifications' => $this->notificationService->getSentNotifications(),
            'inventory' => $this->inventoryService->getAllProducts()
        ];
    }

    /**
     * Test per OrderShipped
     */
    private function testOrderShipped(): array
    {
        $event = new OrderShipped(
            'order-123',
            'customer-456',
            'TRK-789',
            'DHL',
            'Via Roma 123, Milano'
        );

        $this->eventBus->publish($event);

        return [
            'event' => $event->toArray(),
            'notifications' => $this->notificationService->getSentNotifications(),
            'orders' => $this->orderService->getAllOrders()
        ];
    }

    /**
     * Test per PaymentProcessed
     */
    private function testPaymentProcessed(): array
    {
        $event = new PaymentProcessed(
            'order-123',
            'customer-456',
            150.00,
            'CREDIT_CARD',
            'TXN-789',
            'EUR'
        );

        $this->eventBus->publish($event);

        return [
            'event' => $event->toArray(),
            'notifications' => $this->notificationService->getSentNotifications(),
            'orders' => $this->orderService->getAllOrders()
        ];
    }

    /**
     * Test per PaymentFailed
     */
    private function testPaymentFailed(): array
    {
        $event = new PaymentFailed(
            'order-123',
            'customer-456',
            150.00,
            'CREDIT_CARD',
            'Insufficient funds',
            'EUR'
        );

        $this->eventBus->publish($event);

        return [
            'event' => $event->toArray(),
            'notifications' => $this->notificationService->getSentNotifications(),
            'orders' => $this->orderService->getAllOrders()
        ];
    }

    /**
     * Test di tutti gli scenari
     */
    private function testAllScenarios(): array
    {
        return [
            'order_confirmed' => $this->testOrderConfirmed(),
            'order_cancelled' => $this->testOrderCancelled(),
            'order_shipped' => $this->testOrderShipped(),
            'payment_processed' => $this->testPaymentProcessed(),
            'payment_failed' => $this->testPaymentFailed(),
            'event_bus_stats' => $this->eventBus->getStatistics(),
            'notification_stats' => $this->notificationService->getStatistics(),
            'inventory_stats' => $this->inventoryService->getStatistics(),
            'billing_stats' => $this->billingService->getStatistics(),
            'order_stats' => $this->orderService->getStatistics()
        ];
    }

    /**
     * Configura i listener per gli eventi
     */
    private function setupEventListeners(): void
    {
        // Listener per OrderConfirmed
        $this->eventBus->subscribe(OrderConfirmed::class, function (OrderConfirmed $event) {
            $this->notificationService->sendOrderConfirmationEmail(
                $event->customerId,
                $event->orderId,
                $event->total,
                $event->items
            );
        });

        $this->eventBus->subscribe(OrderConfirmed::class, function (OrderConfirmed $event) {
            foreach ($event->items as $item) {
                $this->inventoryService->decreaseStock($item['productId'], $item['quantity']);
            }
        });

        $this->eventBus->subscribe(OrderConfirmed::class, function (OrderConfirmed $event) {
            $this->billingService->createInvoice(
                $event->orderId,
                $event->customerId,
                $event->total,
                $event->items
            );
        });

        // Listener per OrderCancelled
        $this->eventBus->subscribe(OrderCancelled::class, function (OrderCancelled $event) {
            $this->notificationService->sendOrderCancellationEmail(
                $event->customerId,
                $event->orderId,
                $event->total,
                $event->reason
            );
        });

        $this->eventBus->subscribe(OrderCancelled::class, function (OrderCancelled $event) {
            foreach ($event->items as $item) {
                $this->inventoryService->increaseStock($item['productId'], $item['quantity']);
            }
        });

        // Listener per OrderShipped
        $this->eventBus->subscribe(OrderShipped::class, function (OrderShipped $event) {
            $this->notificationService->sendShippingNotification(
                $event->customerId,
                $event->orderId,
                $event->trackingNumber,
                $event->carrier,
                $event->estimatedDelivery
            );
        });

        $this->eventBus->subscribe(OrderShipped::class, function (OrderShipped $event) {
            $this->orderService->updateStatus(
                $event->orderId,
                'SHIPPED',
                [
                    'trackingNumber' => $event->trackingNumber,
                    'carrier' => $event->carrier,
                    'shippedAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
                ]
            );
        });

        // Listener per PaymentProcessed
        $this->eventBus->subscribe(PaymentProcessed::class, function (PaymentProcessed $event) {
            $this->orderService->updateStatus(
                $event->orderId,
                'PAID',
                [
                    'paymentMethod' => $event->paymentMethod,
                    'transactionId' => $event->transactionId,
                    'paidAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
                ]
            );
        });

        // Listener per PaymentFailed
        $this->eventBus->subscribe(PaymentFailed::class, function (PaymentFailed $event) {
            $this->orderService->updateStatus(
                $event->orderId,
                'PAYMENT_FAILED',
                [
                    'paymentMethod' => $event->paymentMethod,
                    'failureReason' => $event->reason,
                    'failedAt' => $event->getOccurredAt()->format('Y-m-d H:i:s')
                ]
            );
        });
    }
}
