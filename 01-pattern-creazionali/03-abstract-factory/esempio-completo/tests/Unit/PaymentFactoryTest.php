<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Payment\Factories\PaymentFactory;
use App\Services\Payment\Factories\StripePaymentFactory;
use App\Services\Payment\Factories\PayPalPaymentFactory;
use App\Services\Payment\Gateways\PaymentGateway;
use App\Services\Payment\Validators\PaymentValidator;
use App\Services\Payment\Loggers\PaymentLogger;

class PaymentFactoryTest extends TestCase
{
    public function test_stripe_factory_creates_compatible_products(): void
    {
        $factory = new StripePaymentFactory('test_key', 'test_secret');
        
        $gateway = $factory->createGateway();
        $validator = $factory->createValidator();
        $logger = $factory->createLogger();
        
        $this->assertInstanceOf(PaymentGateway::class, $gateway);
        $this->assertInstanceOf(PaymentValidator::class, $validator);
        $this->assertInstanceOf(PaymentLogger::class, $logger);
        $this->assertEquals('stripe', $factory->getProviderName());
    }
    
    public function test_paypal_factory_creates_compatible_products(): void
    {
        $factory = new PayPalPaymentFactory('test_client_id', 'test_client_secret');
        
        $gateway = $factory->createGateway();
        $validator = $factory->createValidator();
        $logger = $factory->createLogger();
        
        $this->assertInstanceOf(PaymentGateway::class, $gateway);
        $this->assertInstanceOf(PaymentValidator::class, $validator);
        $this->assertInstanceOf(PaymentLogger::class, $logger);
        $this->assertEquals('paypal', $factory->getProviderName());
    }
    
    public function test_stripe_products_work_together(): void
    {
        $factory = new StripePaymentFactory('test_key', 'test_secret');
        
        $gateway = $factory->createGateway();
        $validator = $factory->createValidator();
        
        // Dati validi per Stripe
        $paymentData = [
            'card_token' => 'tok_test_123',
            'currency' => 'USD',
            'amount' => 10.00,
            'customer' => [
                'email' => 'test@example.com',
                'name' => 'Test User'
            ]
        ];
        
        // La validazione dovrebbe passare
        $validationResult = $validator->validate($paymentData);
        $this->assertTrue($validationResult->valid);
        
        // Il pagamento dovrebbe essere processato
        $result = $gateway->processPayment(10.00, $paymentData);
        $this->assertTrue($result->success);
        $this->assertStringStartsWith('stripe_', $result->transactionId);
    }
    
    public function test_paypal_products_work_together(): void
    {
        $factory = new PayPalPaymentFactory('test_client_id', 'test_client_secret');
        
        $gateway = $factory->createGateway();
        $validator = $factory->createValidator();
        
        // Dati validi per PayPal
        $paymentData = [
            'paypal_order_id' => 'PAYID-123456',
            'currency' => 'USD',
            'amount' => 10.00,
            'customer' => [
                'email' => 'test@example.com'
            ]
        ];
        
        // La validazione dovrebbe passare
        $validationResult = $validator->validate($paymentData);
        $this->assertTrue($validationResult->valid);
        
        // Il pagamento dovrebbe essere processato
        $result = $gateway->processPayment(10.00, $paymentData);
        $this->assertTrue($result->success);
        $this->assertStringStartsWith('paypal_', $result->transactionId);
    }
    
    public function test_cross_provider_incompatibility(): void
    {
        $stripeFactory = new StripePaymentFactory('test_key', 'test_secret');
        $paypalFactory = new PayPalPaymentFactory('test_client_id', 'test_client_secret');
        
        $stripeValidator = $stripeFactory->createValidator();
        $paypalGateway = $paypalFactory->createGateway();
        
        // Dati Stripe con gateway PayPal dovrebbero fallire
        $stripeData = [
            'card_token' => 'tok_test_123',
            'currency' => 'USD',
            'amount' => 10.00
        ];
        
        $result = $paypalGateway->processPayment(10.00, $stripeData);
        $this->assertFalse($result->success);
        $this->assertStringContains('Invalid payment data for PayPal', $result->message);
    }
}

