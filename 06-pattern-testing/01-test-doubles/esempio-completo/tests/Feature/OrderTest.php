<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\PaymentServiceInterface;
use App\Services\NotificationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake delle notifiche per i test di integrazione
        Notification::fake();
    }

    /** @test */
    public function it_creates_order_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $orderData = [
            'total_amount' => 99.99,
            'payment_method' => Order::PAYMENT_METHOD_CREDIT_CARD,
            'shipping_address' => [
                'street' => 'Via Roma 1',
                'city' => 'Milano',
                'postal_code' => '20100',
                'country' => 'Italia'
            ],
            'billing_address' => [
                'street' => 'Via Roma 1',
                'city' => 'Milano',
                'postal_code' => '20100',
                'country' => 'Italia'
            ],
            'notes' => 'Test order via API'
        ];

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/orders', $orderData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'total_amount',
                    'status',
                    'payment_method',
                    'shipping_address',
                    'billing_address',
                    'notes',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PENDING
        ]);
    }

    /** @test */
    public function it_processes_payment_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING
        ]);

        $paymentData = [
            'method' => 'credit_card',
            'card_token' => 'tok_123456'
        ];

        // Mock del payment service
        $this->mock(PaymentServiceInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('processPayment')
                ->once()
                ->with($order, \Mockery::type('array'))
                ->andReturn(true);
        });

        // Act
        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/payment", $paymentData);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }

    /** @test */
    public function it_cancels_order_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PAID
        ]);

        $cancelData = [
            'reason' => 'Customer request'
        ];

        // Mock dei servizi
        $this->mock(PaymentServiceInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('refundPayment')
                ->once()
                ->with($order, null)
                ->andReturn(true);
        });

        $this->mock(NotificationServiceInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('sendOrderUpdate')
                ->once()
                ->with($order, \Mockery::type('string'))
                ->andReturn(true);
        });

        // Act
        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/cancel", $cancelData);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
    }

    /** @test */
    public function it_gets_user_orders_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user)
            ->getJson('/api/orders');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'total_amount',
                        'status',
                        'created_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_gets_single_order_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user)
            ->getJson("/api/orders/{$order->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'user_id' => $user->id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status
                ]
            ]);
    }

    /** @test */
    public function it_updates_order_status_through_api()
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PAID
        ]);

        $statusData = [
            'status' => Order::STATUS_SHIPPED
        ];

        // Mock del notification service
        $this->mock(NotificationServiceInterface::class, function ($mock) use ($order) {
            $mock->shouldReceive('sendOrderUpdate')
                ->once()
                ->with($order, \Mockery::type('string'))
                ->andReturn(true);
        });

        // Act
        $response = $this->actingAs($user)
            ->putJson("/api/orders/{$order->id}/status", $statusData);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_SHIPPED, $order->status);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_orders()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        // Act
        $response = $this->actingAs($user2)
            ->getJson("/api/orders/{$order->id}");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_order_creation_data()
    {
        // Arrange
        $user = User::factory()->create();
        $invalidData = [
            'total_amount' => 'invalid',
            'payment_method' => 'invalid_method'
        ];

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/orders', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'total_amount',
                'payment_method',
                'shipping_address',
                'billing_address'
            ]);
    }

    /** @test */
    public function it_handles_payment_failure_gracefully()
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING
        ]);

        $paymentData = [
            'method' => 'credit_card',
            'card_token' => 'tok_invalid'
        ];

        // Mock del payment service che fallisce
        $this->mock(PaymentServiceInterface::class, function ($mock) {
            $mock->shouldReceive('processPayment')
                ->once()
                ->andReturn(false);
        });

        // Act
        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/payment", $paymentData);

        // Assert
        $response->assertStatus(400)
            ->assertJson(['success' => false]);

        $order->refresh();
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
    }
}
