<?php

namespace Tests\Feature;

use Tests\TestCase;
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
 * Test per il Domain Event Pattern
 * 
 * Questi test dimostrano come il Domain Event Pattern
 * disaccoppia i servizi attraverso eventi e listener.
 */
class DomainEventPatternTest extends TestCase
{
    private EventBus $eventBus;
    private NotificationService $notificationService;
    private InventoryService $inventoryService;
    private BillingService $billingService;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventBus = new EventBus();
        $this->notificationService = new NotificationService();
        $this->inventoryService = new InventoryService();
        $this->billingService = new BillingService();
        $this->orderService = new OrderService();
        
        $this->setupEventListeners();
    }

    /** @test */
    public function it_creates_domain_events()
    {
        $event = new OrderConfirmed('order-1', 'customer-1', 100.00);
        
        $this->assertEquals('order-1', $event->orderId);
        $this->assertEquals('customer-1', $event->customerId);
        $this->assertEquals(100.00, $event->total);
        $this->assertNotNull($event->getEventId());
        $this->assertNotNull($event->getOccurredAt());
    }

    /** @test */
    public function it_publishes_events_to_listeners()
    {
        $event = new OrderConfirmed('order-1', 'customer-1', 100.00, [
            ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00]
        ]);

        $this->eventBus->publish($event);

        // Verifica che i listener siano stati chiamati
        $notifications = $this->notificationService->getSentNotifications();
        $this->assertCount(1, $notifications);
        $this->assertEquals('order_confirmation', $notifications[0]['type']);

        $inventory = $this->inventoryService->getAllProducts();
        $this->assertEquals(98, $inventory['PROD-001']['stock']); // 100 - 2

        $invoices = $this->billingService->getAllInvoices();
        $this->assertCount(1, $invoices);
    }

    /** @test */
    public function it_handles_order_cancelled_events()
    {
        $event = new OrderCancelled('order-1', 'customer-1', 100.00, 'Customer request', [
            ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00]
        ]);

        $this->eventBus->publish($event);

        // Verifica che i listener siano stati chiamati
        $notifications = $this->notificationService->getSentNotifications();
        $this->assertCount(1, $notifications);
        $this->assertEquals('order_cancellation', $notifications[0]['type']);

        $inventory = $this->inventoryService->getAllProducts();
        $this->assertEquals(102, $inventory['PROD-001']['stock']); // 100 + 2
    }

    /** @test */
    public function it_handles_order_shipped_events()
    {
        $event = new OrderShipped('order-1', 'customer-1', 'TRK-123', 'DHL');

        $this->eventBus->publish($event);

        // Verifica che i listener siano stati chiamati
        $notifications = $this->notificationService->getSentNotifications();
        $this->assertCount(1, $notifications);
        $this->assertEquals('shipping_notification', $notifications[0]['type']);

        $orders = $this->orderService->getAllOrders();
        $this->assertCount(1, $orders);
        $this->assertEquals('SHIPPED', $orders['order-1']['status']);
    }

    /** @test */
    public function it_handles_payment_processed_events()
    {
        $event = new PaymentProcessed('order-1', 'customer-1', 100.00, 'CREDIT_CARD', 'TXN-123');

        $this->eventBus->publish($event);

        // Verifica che i listener siano stati chiamati
        $orders = $this->orderService->getAllOrders();
        $this->assertCount(1, $orders);
        $this->assertEquals('PAID', $orders['order-1']['status']);
    }

    /** @test */
    public function it_handles_payment_failed_events()
    {
        $event = new PaymentFailed('order-1', 'customer-1', 100.00, 'CREDIT_CARD', 'Insufficient funds');

        $this->eventBus->publish($event);

        // Verifica che i listener siano stati chiamati
        $orders = $this->orderService->getAllOrders();
        $this->assertCount(1, $orders);
        $this->assertEquals('PAYMENT_FAILED', $orders['order-1']['status']);
    }

    /** @test */
    public function it_handles_multiple_events()
    {
        $events = [
            new OrderConfirmed('order-1', 'customer-1', 100.00, [
                ['productId' => 'PROD-001', 'quantity' => 1, 'price' => 100.00]
            ]),
            new OrderShipped('order-1', 'customer-1', 'TRK-123', 'DHL'),
            new PaymentProcessed('order-1', 'customer-1', 100.00, 'CREDIT_CARD', 'TXN-123')
        ];

        $this->eventBus->publishMultiple($events);

        // Verifica che tutti i listener siano stati chiamati
        $notifications = $this->notificationService->getSentNotifications();
        $this->assertCount(3, $notifications);

        $orders = $this->orderService->getAllOrders();
        $this->assertCount(1, $orders);
        $this->assertEquals('PAID', $orders['order-1']['status']);
    }

    /** @test */
    public function it_handles_event_subscription()
    {
        $eventType = OrderConfirmed::class;
        
        $this->assertTrue($this->eventBus->hasListeners($eventType));
        $this->assertEquals(3, $this->eventBus->getListenerCount($eventType));
    }

    /** @test */
    public function it_handles_event_unsubscription()
    {
        $eventType = OrderConfirmed::class;
        $listeners = $this->eventBus->getListeners($eventType);
        
        $this->eventBus->unsubscribe($eventType, $listeners[0]);
        
        $this->assertEquals(2, $this->eventBus->getListenerCount($eventType));
    }

    /** @test */
    public function it_handles_event_history()
    {
        $event1 = new OrderConfirmed('order-1', 'customer-1', 100.00);
        $event2 = new OrderCancelled('order-2', 'customer-2', 200.00);
        
        $this->eventBus->publish($event1);
        $this->eventBus->publish($event2);
        
        $history = $this->eventBus->getEventHistory();
        $this->assertCount(2, $history);
        
        $orderConfirmedHistory = $this->eventBus->getEventHistoryByType(OrderConfirmed::class);
        $this->assertCount(1, $orderConfirmedHistory);
    }

    /** @test */
    public function it_handles_event_statistics()
    {
        $event1 = new OrderConfirmed('order-1', 'customer-1', 100.00);
        $event2 = new OrderConfirmed('order-2', 'customer-2', 200.00);
        $event3 = new OrderCancelled('order-3', 'customer-3', 300.00);
        
        $this->eventBus->publish($event1);
        $this->eventBus->publish($event2);
        $this->eventBus->publish($event3);
        
        $stats = $this->eventBus->getStatistics();
        
        $this->assertEquals(3, $stats['totalEvents']);
        $this->assertEquals(2, $stats['eventTypes'][OrderConfirmed::class]);
        $this->assertEquals(1, $stats['eventTypes'][OrderCancelled::class]);
    }

    /** @test */
    public function it_handles_web_interface()
    {
        $response = $this->get('/domain-event');
        
        $response->assertStatus(200);
        $response->assertSee('Domain Event Pattern');
    }

    /** @test */
    public function it_handles_api_test_endpoint()
    {
        $response = $this->postJson('/api/domain-event/test', [
            'type' => 'order-confirmed'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_order_confirmation_api()
    {
        $response = $this->postJson('/api/domain-event/order/confirm', [
            'orderId' => 'order-123',
            'customerId' => 'customer-456',
            'total' => 150.00,
            'items' => [
                ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00],
                ['productId' => 'PROD-002', 'quantity' => 1, 'price' => 50.00]
            ]
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_order_cancellation_api()
    {
        $response = $this->postJson('/api/domain-event/order/cancel', [
            'orderId' => 'order-123',
            'customerId' => 'customer-456',
            'total' => 150.00,
            'reason' => 'Customer request'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_order_shipping_api()
    {
        $response = $this->postJson('/api/domain-event/order/ship', [
            'orderId' => 'order-123',
            'customerId' => 'customer-456',
            'trackingNumber' => 'TRK-789',
            'carrier' => 'DHL'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_payment_processing_api()
    {
        $response = $this->postJson('/api/domain-event/payment/process', [
            'orderId' => 'order-123',
            'customerId' => 'customer-456',
            'amount' => 150.00,
            'paymentMethod' => 'CREDIT_CARD',
            'transactionId' => 'TXN-789'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_payment_failure_api()
    {
        $response = $this->postJson('/api/domain-event/payment/fail', [
            'orderId' => 'order-123',
            'customerId' => 'customer-456',
            'amount' => 150.00,
            'paymentMethod' => 'CREDIT_CARD',
            'reason' => 'Insufficient funds'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_serializes_events_correctly()
    {
        $event = new OrderConfirmed('order-1', 'customer-1', 100.00, [
            ['productId' => 'PROD-001', 'quantity' => 2, 'price' => 50.00]
        ]);
        
        $array = $event->toArray();
        
        $this->assertArrayHasKey('eventId', $array);
        $this->assertArrayHasKey('eventName', $array);
        $this->assertArrayHasKey('occurredAt', $array);
        $this->assertArrayHasKey('orderId', $array);
        $this->assertArrayHasKey('customerId', $array);
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('items', $array);
    }

    /** @test */
    public function it_handles_service_statistics()
    {
        // Genera alcuni eventi per testare le statistiche
        $this->eventBus->publish(new OrderConfirmed('order-1', 'customer-1', 100.00));
        $this->eventBus->publish(new OrderCancelled('order-2', 'customer-2', 200.00));
        
        $notificationStats = $this->notificationService->getStatistics();
        $inventoryStats = $this->inventoryService->getStatistics();
        $billingStats = $this->billingService->getStatistics();
        $orderStats = $this->orderService->getStatistics();
        
        $this->assertArrayHasKey('total', $notificationStats);
        $this->assertArrayHasKey('totalProducts', $inventoryStats);
        $this->assertArrayHasKey('totalInvoices', $billingStats);
        $this->assertArrayHasKey('totalOrders', $orderStats);
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
