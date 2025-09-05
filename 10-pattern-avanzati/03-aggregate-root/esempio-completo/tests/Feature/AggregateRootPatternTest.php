<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Aggregates\Order;
use App\ValueObjects\OrderAddress;
use App\ValueObjects\OrderPayment;
use App\Repositories\OrderRepository;

/**
 * Test per il Aggregate Root Pattern
 * 
 * Questi test dimostrano come l'Aggregate Root Pattern
 * controlla tutte le modifiche e garantisce la consistenza
 * dei dati attraverso regole di business centralizzate.
 */
class AggregateRootPatternTest extends TestCase
{
    private OrderRepository $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = new OrderRepository();
    }

    /** @test */
    public function it_creates_order_aggregate()
    {
        $order = new Order('order-1', 'customer-123');
        
        $this->assertEquals('order-1', $order->getId());
        $this->assertEquals('customer-123', $order->getCustomerId());
        $this->assertEquals('DRAFT', $order->getStatus());
        $this->assertTrue($order->canBeModified());
    }

    /** @test */
    public function it_adds_items_to_order()
    {
        $order = new Order('order-1', 'customer-123');
        
        $order->addItem('PROD-001', 2, 10.50);
        $order->addItem('PROD-002', 1, 25.00);
        
        $this->assertCount(2, $order->getItems());
        $this->assertEquals(46.00, $order->getTotal());
    }

    /** @test */
    public function it_updates_item_quantities()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $order->updateItemQuantity('PROD-001', 5);
        
        $item = $order->getItems()->first();
        $this->assertEquals(5, $item->getQuantity());
        $this->assertEquals(52.50, $order->getTotal());
    }

    /** @test */
    public function it_removes_items_from_order()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        $order->addItem('PROD-002', 1, 25.00);
        
        $order->removeItem('PROD-001');
        
        $this->assertCount(1, $order->getItems());
        $this->assertEquals(25.00, $order->getTotal());
    }

    /** @test */
    public function it_sets_addresses()
    {
        $order = new Order('order-1', 'customer-123');
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $this->assertNotNull($order->getShippingAddress());
        $this->assertNotNull($order->getBillingAddress());
    }

    /** @test */
    public function it_sets_payment()
    {
        $order = new Order('order-1', 'customer-123');
        $payment = new OrderPayment('CREDIT_CARD', 'PENDING', 'TXN-123', '1234', 'VISA');
        
        $order->setPayment($payment);
        
        $this->assertNotNull($order->getPayment());
        $this->assertEquals('CREDIT_CARD', $order->getPayment()->getMethod());
    }

    /** @test */
    public function it_confirms_order()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $order->confirm();
        
        $this->assertEquals('CONFIRMED', $order->getStatus());
        $this->assertNotNull($order->getConfirmedAt());
        $this->assertTrue($order->hasDomainEvents());
    }

    /** @test */
    public function it_cancels_order()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $order->cancel();
        
        $this->assertEquals('CANCELLED', $order->getStatus());
        $this->assertNotNull($order->getCancelledAt());
        $this->assertTrue($order->hasDomainEvents());
    }

    /** @test */
    public function it_validates_business_rules()
    {
        $order = new Order('order-1', 'customer-123');
        
        // Ordine vuoto non può essere confermato
        $this->assertFalse($order->canBeConfirmed());
        
        // Aggiungi item ma senza indirizzi
        $order->addItem('PROD-001', 1, 10.00);
        $this->assertFalse($order->canBeConfirmed());
        
        // Aggiungi indirizzi
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $this->assertTrue($order->canBeConfirmed());
    }

    /** @test */
    public function it_prevents_modification_after_confirmation()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $order->confirm();
        
        $this->assertFalse($order->canBeModified());
        
        $this->expectException(\InvalidArgumentException::class);
        $order->addItem('PROD-002', 1, 25.00);
    }

    /** @test */
    public function it_prevents_cancellation_after_shipping()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $order->confirm();
        
        // Simula spedizione (cambia status manualmente per test)
        $reflection = new \ReflectionClass($order);
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($order, 'SHIPPED');
        
        $this->assertFalse($order->canBeCancelled());
    }

    /** @test */
    public function it_emits_domain_events()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $order->confirm();
        
        $events = $order->getDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('OrderConfirmed', $events->first()->toArray()['event']);
    }

    /** @test */
    public function it_handles_repository_operations()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $this->orderRepository->save($order);
        
        $savedOrder = $this->orderRepository->findById('order-1');
        $this->assertNotNull($savedOrder);
        $this->assertEquals('order-1', $savedOrder->getId());
    }

    /** @test */
    public function it_handles_web_interface()
    {
        $response = $this->get('/aggregate-root');
        
        $response->assertStatus(200);
        $response->assertSee('Aggregate Root Pattern');
    }

    /** @test */
    public function it_handles_api_test_endpoint()
    {
        $response = $this->postJson('/api/aggregate-root/test', [
            'type' => 'order'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_order_creation_api()
    {
        $response = $this->postJson('/api/aggregate-root/order/create', [
            'customerId' => 'customer-123'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_add_item_api()
    {
        // Prima crea un ordine
        $createResponse = $this->postJson('/api/aggregate-root/order/create', [
            'customerId' => 'customer-123'
        ]);
        
        $orderId = $createResponse->json('data.id');
        
        // Poi aggiungi un item
        $response = $this->postJson("/api/aggregate-root/order/{$orderId}/add-item", [
            'productId' => 'PROD-001',
            'quantity' => 2,
            'price' => 10.50
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_order_confirmation_api()
    {
        // Prima crea un ordine
        $createResponse = $this->postJson('/api/aggregate-root/order/create', [
            'customerId' => 'customer-123'
        ]);
        
        $orderId = $createResponse->json('data.id');
        
        // Aggiungi un item
        $this->postJson("/api/aggregate-root/order/{$orderId}/add-item", [
            'productId' => 'PROD-001',
            'quantity' => 2,
            'price' => 10.50
        ]);
        
        // Poi conferma l'ordine
        $response = $this->postJson("/api/aggregate-root/order/{$orderId}/confirm", [
            'shippingAddress' => [
                'street' => 'Via Roma 123',
                'city' => 'Milano',
                'postalCode' => '20100',
                'country' => 'IT',
                'state' => 'Lombardia'
            ],
            'billingAddress' => [
                'street' => 'Via Roma 123',
                'city' => 'Milano',
                'postalCode' => '20100',
                'country' => 'IT',
                'state' => 'Lombardia'
            ],
            'payment' => [
                'method' => 'CREDIT_CARD',
                'status' => 'PENDING',
                'transactionId' => 'TXN-123',
                'cardLastFour' => '1234',
                'cardBrand' => 'VISA'
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
        // Prima crea un ordine
        $createResponse = $this->postJson('/api/aggregate-root/order/create', [
            'customerId' => 'customer-123'
        ]);
        
        $orderId = $createResponse->json('data.id');
        
        // Poi cancella l'ordine
        $response = $this->postJson("/api/aggregate-root/order/{$orderId}/cancel");
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_serializes_order_correctly()
    {
        $order = new Order('order-1', 'customer-123');
        $order->addItem('PROD-001', 2, 10.50);
        
        $address = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($address);
        $order->setBillingAddress($address);
        
        $payment = new OrderPayment('CREDIT_CARD', 'PENDING', 'TXN-123', '1234', 'VISA');
        $order->setPayment($payment);
        
        $order->confirm();
        
        $array = $order->toArray();
        
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('customerId', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('items', $array);
        $this->assertArrayHasKey('shippingAddress', $array);
        $this->assertArrayHasKey('billingAddress', $array);
        $this->assertArrayHasKey('payment', $array);
        $this->assertArrayHasKey('confirmedAt', $array);
    }
}
