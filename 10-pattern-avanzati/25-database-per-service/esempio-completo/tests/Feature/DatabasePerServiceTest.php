<?php

namespace Tests\Feature;

use App\Services\EventBusService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabasePerServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventBusService $eventBus;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventBus = new EventBusService();
        $this->userService = new UserService($this->eventBus);
        $this->productService = new ProductService($this->eventBus);
        $this->orderService = new OrderService($this->eventBus);
        $this->paymentService = new PaymentService($this->eventBus);
    }

    /** @test */
    public function it_can_create_user()
    {
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertEquals('user_service', $user['database']);
    }

    /** @test */
    public function it_can_create_product()
    {
        $product = $this->productService->createProduct([
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ]);

        $this->assertIsArray($product);
        $this->assertEquals('Test Product', $product['name']);
        $this->assertEquals(29.99, $product['price']);
        $this->assertEquals('product_service', $product['database']);
    }

    /** @test */
    public function it_can_create_order()
    {
        $order = $this->orderService->createOrder([
            'user_id' => 1,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 29.99
                ]
            ],
            'total' => 59.98
        ]);

        $this->assertIsArray($order);
        $this->assertEquals(1, $order['user_id']);
        $this->assertEquals(59.98, $order['total']);
        $this->assertEquals('order_service', $order['database']);
    }

    /** @test */
    public function it_can_create_payment()
    {
        $payment = $this->paymentService->createPayment([
            'order_id' => 1,
            'user_id' => 1,
            'amount' => 59.98,
            'method' => 'credit_card'
        ]);

        $this->assertIsArray($payment);
        $this->assertEquals(1, $payment['order_id']);
        $this->assertEquals(59.98, $payment['amount']);
        $this->assertEquals('payment_service', $payment['database']);
    }

    /** @test */
    public function it_can_publish_and_subscribe_events()
    {
        $eventReceived = false;
        $eventData = null;

        $this->eventBus->subscribe('TestEvent', function ($event) use (&$eventReceived, &$eventData) {
            $eventReceived = true;
            $eventData = $event;
        });

        $this->eventBus->publish('TestEvent', ['test' => 'data']);

        $this->assertTrue($eventReceived);
        $this->assertEquals('TestEvent', $eventData['type']);
        $this->assertEquals(['test' => 'data'], $eventData['data']);
    }

    /** @test */
    public function it_can_get_event_stats()
    {
        $this->eventBus->publish('TestEvent1', ['data' => 'test1']);
        $this->eventBus->publish('TestEvent2', ['data' => 'test2']);

        $stats = $this->eventBus->getEventStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_events', $stats);
        $this->assertArrayHasKey('published_events', $stats);
        $this->assertArrayHasKey('failed_events', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
    }

    /** @test */
    public function it_can_get_service_stats()
    {
        $userStats = $this->userService->getStats();
        $productStats = $this->productService->getStats();
        $orderStats = $this->orderService->getStats();
        $paymentStats = $this->paymentService->getStats();

        $this->assertIsArray($userStats);
        $this->assertEquals('UserService', $userStats['service']);
        $this->assertEquals('user_service', $userStats['database']);

        $this->assertIsArray($productStats);
        $this->assertEquals('ProductService', $productStats['service']);
        $this->assertEquals('product_service', $productStats['database']);

        $this->assertIsArray($orderStats);
        $this->assertEquals('OrderService', $orderStats['service']);
        $this->assertEquals('order_service', $orderStats['database']);

        $this->assertIsArray($paymentStats);
        $this->assertEquals('PaymentService', $paymentStats['service']);
        $this->assertEquals('payment_service', $paymentStats['database']);
    }

    /** @test */
    public function it_can_update_product_inventory()
    {
        $product = $this->productService->createProduct([
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ]);

        $updatedProduct = $this->productService->updateInventory($product['id'], -2);

        $this->assertIsArray($updatedProduct);
        $this->assertEquals(8, $updatedProduct['inventory']);
        $this->assertEquals(-2, $updatedProduct['quantity_change']);
    }

    /** @test */
    public function it_can_update_order_status()
    {
        $order = $this->orderService->createOrder([
            'user_id' => 1,
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 29.99]],
            'total' => 29.99
        ]);

        $updatedOrder = $this->orderService->updateOrderStatus($order['id'], 'paid');

        $this->assertIsArray($updatedOrder);
        $this->assertEquals('paid', $updatedOrder['status']);
    }

    /** @test */
    public function it_can_process_payment()
    {
        $payment = $this->paymentService->createPayment([
            'order_id' => 1,
            'user_id' => 1,
            'amount' => 59.98,
            'method' => 'credit_card'
        ]);

        $processedPayment = $this->paymentService->processPayment($payment['id']);

        $this->assertIsArray($processedPayment);
        $this->assertContains($processedPayment['status'], ['completed', 'failed']);
    }

    /** @test */
    public function it_handles_event_communication_between_services()
    {
        // Crea un utente
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Crea un prodotto
        $product = $this->productService->createProduct([
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ]);

        // Crea un ordine (dovrebbe generare eventi)
        $order = $this->orderService->createOrder([
            'user_id' => $user['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 2,
                    'price' => $product['price']
                ]
            ],
            'total' => $product['price'] * 2
        ]);

        // Verifica che l'ordine sia stato creato
        $this->assertIsArray($order);
        $this->assertEquals($user['id'], $order['user_id']);

        // Verifica che gli eventi siano stati pubblicati
        $eventStats = $this->eventBus->getEventStats();
        $this->assertGreaterThan(0, $eventStats['total_events']);
    }

    /** @test */
    public function it_has_unique_pattern_ids()
    {
        $userServiceId = $this->userService->getId();
        $productServiceId = $this->productService->getId();
        $orderServiceId = $this->orderService->getId();
        $paymentServiceId = $this->paymentService->getId();
        $eventBusId = $this->eventBus->getId();

        $this->assertStringStartsWith('user-service-pattern-', $userServiceId);
        $this->assertStringStartsWith('product-service-pattern-', $productServiceId);
        $this->assertStringStartsWith('order-service-pattern-', $orderServiceId);
        $this->assertStringStartsWith('payment-service-pattern-', $paymentServiceId);
        $this->assertStringStartsWith('event-bus-pattern-', $eventBusId);

        // Tutti gli ID dovrebbero essere unici
        $ids = [$userServiceId, $productServiceId, $orderServiceId, $paymentServiceId, $eventBusId];
        $this->assertEquals(count($ids), count(array_unique($ids)));
    }
}
