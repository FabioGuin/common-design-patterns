<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurazione per i test
        config([
            'services.payment.gateway_url' => 'https://api.test-payment.com',
            'services.payment.api_key' => 'test_key_123'
        ]);

        $this->paymentService = new PaymentService();
    }

    /** @test */
    public function it_processes_payment_successfully()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PENDING
        ]);

        $paymentData = [
            'method' => 'credit_card',
            'card_token' => 'tok_123456'
        ];

        // Act
        $result = $this->paymentService->processPayment($order, $paymentData);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }

    /** @test */
    public function it_handles_payment_with_different_methods()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 50.00,
            'status' => Order::STATUS_PENDING
        ]);

        $paymentMethods = [
            ['method' => 'credit_card', 'card_token' => 'tok_123'],
            ['method' => 'paypal', 'paypal_id' => 'pay_123'],
            ['method' => 'bank_transfer', 'account' => 'acc_123']
        ];

        foreach ($paymentMethods as $paymentData) {
            // Reset order status
            $order->status = Order::STATUS_PENDING;

            // Act
            $result = $this->paymentService->processPayment($order, $paymentData);

            // Assert
            $this->assertTrue($result, "Payment failed for method: {$paymentData['method']}");
            $this->assertEquals(Order::STATUS_PAID, $order->status);
        }
    }

    /** @test */
    public function it_refunds_payment_successfully()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 100.00,
            'status' => Order::STATUS_PAID
        ]);

        // Act
        $result = $this->paymentService->refundPayment($order);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_refunds_partial_amount()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 100.00,
            'status' => Order::STATUS_PAID
        ]);

        $partialAmount = 50.00;

        // Act
        $result = $this->paymentService->refundPayment($order, $partialAmount);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_gets_payment_status()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PAID
        ]);

        // Act
        $status = $this->paymentService->getPaymentStatus($order);

        // Assert
        $this->assertEquals('completed', $status);
    }

    /** @test */
    public function it_handles_payment_without_card_token()
    {
        // Arrange
        $order = new Order([
            'id' => 1,
            'total_amount' => 75.50,
            'status' => Order::STATUS_PENDING
        ]);

        $paymentData = [
            'method' => 'credit_card'
            // Nessun card_token
        ];

        // Act
        $result = $this->paymentService->processPayment($order, $paymentData);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }
}
