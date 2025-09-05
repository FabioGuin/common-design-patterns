<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Repositories\OrderRepositoryInterface;
use App\Services\OrderService;
use App\Services\PaymentServiceInterface;
use App\Services\NotificationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private $mockOrderRepository;
    private $mockPaymentService;
    private $mockNotificationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Creazione dei mock objects
        $this->mockOrderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->mockPaymentService = Mockery::mock(PaymentServiceInterface::class);
        $this->mockNotificationService = Mockery::mock(NotificationServiceInterface::class);

        // Iniezione delle dipendenze mock
        $this->orderService = new OrderService(
            $this->mockOrderRepository,
            $this->mockPaymentService,
            $this->mockNotificationService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_order_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $orderData = [
            'total_amount' => 99.99,
            'payment_method' => Order::PAYMENT_METHOD_CREDIT_CARD,
            'shipping_address' => ['street' => 'Via Roma 1', 'city' => 'Milano'],
            'billing_address' => ['street' => 'Via Roma 1', 'city' => 'Milano'],
            'notes' => 'Test order'
        ];

        $expectedOrder = new Order($orderData);
        $expectedOrder->id = 1;
        $expectedOrder->user_id = $user->id;

        // Mock del repository - STUB
        $this->mockOrderRepository
            ->shouldReceive('create')
            ->once()
            ->with(array_merge($orderData, ['user_id' => $user->id]))
            ->andReturn($expectedOrder);

        // Act
        $result = $this->orderService->createOrder($user, $orderData);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($user->id, $result->user_id);
        $this->assertEquals(99.99, $result->total_amount);
    }

    /** @test */
    public function it_processes_payment_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $order = new Order([
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PENDING
        ]);
        $order->id = 1;

        $paymentData = [
            'method' => 'credit_card',
            'card_token' => 'tok_123456'
        ];

        // Mock del payment service - MOCK con verifica
        $this->mockPaymentService
            ->shouldReceive('processPayment')
            ->once()
            ->with($order, $paymentData)
            ->andReturn(true);

        // Mock del notification service - SPY
        $this->mockNotificationService
            ->shouldReceive('sendOrderConfirmation')
            ->once()
            ->with($order)
            ->andReturn(true);

        // Act
        $result = $this->orderService->processPayment($order, $paymentData);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_payment_failure()
    {
        // Arrange
        $user = User::factory()->create();
        $order = new Order([
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PENDING
        ]);
        $order->id = 1;

        $paymentData = ['method' => 'credit_card'];

        // Mock del payment service che fallisce
        $this->mockPaymentService
            ->shouldReceive('processPayment')
            ->once()
            ->andReturn(false);

        // Mock del notification service che NON deve essere chiamato
        $this->mockNotificationService
            ->shouldNotReceive('sendOrderConfirmation');

        // Act
        $result = $this->orderService->processPayment($order, $paymentData);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_cancels_order_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $order = new Order([
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PAID
        ]);
        $order->id = 1;

        // Mock del payment service per refund
        $this->mockPaymentService
            ->shouldReceive('refundPayment')
            ->once()
            ->with($order, null)
            ->andReturn(true);

        // Mock del notification service
        $this->mockNotificationService
            ->shouldReceive('sendOrderUpdate')
            ->once()
            ->with($order, Mockery::pattern('/Ordine cancellato/'))
            ->andReturn(true);

        // Act
        $result = $this->orderService->cancelOrder($order, 'Customer request');

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
    }

    /** @test */
    public function it_throws_exception_when_cancelling_non_cancellable_order()
    {
        // Arrange
        $user = User::factory()->create();
        $order = new Order([
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_SHIPPED // Non cancellabile
        ]);
        $order->id = 1;

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order cannot be cancelled');

        $this->orderService->cancelOrder($order);
    }

    /** @test */
    public function it_updates_order_status_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $order = new Order([
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PAID
        ]);
        $order->id = 1;

        // Mock del notification service
        $this->mockNotificationService
            ->shouldReceive('sendOrderUpdate')
            ->once()
            ->with($order, Mockery::pattern('/Stato aggiornato da paid a shipped/'))
            ->andReturn(true);

        // Act
        $result = $this->orderService->updateOrderStatus($order, Order::STATUS_SHIPPED);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_SHIPPED, $order->status);
    }

    /** @test */
    public function it_gets_user_orders()
    {
        // Arrange
        $user = User::factory()->create();
        $orders = collect([
            new Order(['user_id' => $user->id, 'total_amount' => 50.00]),
            new Order(['user_id' => $user->id, 'total_amount' => 75.00])
        ]);

        // Mock del repository
        $this->mockOrderRepository
            ->shouldReceive('findByUser')
            ->once()
            ->with($user)
            ->andReturn($orders);

        // Act
        $result = $this->orderService->getUserOrders($user);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($orders, $result);
    }

    /** @test */
    public function it_gets_order_by_id()
    {
        // Arrange
        $order = new Order(['user_id' => 1, 'total_amount' => 99.99]);
        $order->id = 123;

        // Mock del repository
        $this->mockOrderRepository
            ->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($order);

        // Act
        $result = $this->orderService->getOrderById(123);

        // Assert
        $this->assertEquals($order, $result);
    }
}
