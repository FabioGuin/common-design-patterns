<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentResult;
use App\Services\Payment\PaymentStatus;

class StripeGateway implements PaymentGateway
{
    public function __construct(
        private string $apiKey,
        private string $webhookSecret
    ) {}
    
    public function processPayment(float $amount, array $data): PaymentResult
    {
        // Simulazione chiamata API Stripe
        $transactionId = 'stripe_' . uniqid();
        
        // Logica di pagamento Stripe
        if ($amount > 0 && isset($data['card_token'])) {
            return PaymentResult::success(
                $transactionId,
                'Payment processed successfully via Stripe',
                ['provider' => 'stripe', 'amount' => $amount]
            );
        }
        
        return PaymentResult::failure('Invalid payment data for Stripe');
    }
    
    public function refundPayment(string $transactionId, float $amount): PaymentResult
    {
        // Simulazione rimborso Stripe
        if (str_starts_with($transactionId, 'stripe_')) {
            return PaymentResult::success(
                $transactionId,
                'Refund processed successfully via Stripe',
                ['refund_amount' => $amount]
            );
        }
        
        return PaymentResult::failure('Invalid transaction ID for Stripe');
    }
    
    public function getPaymentStatus(string $transactionId): PaymentStatus
    {
        // Simulazione verifica stato Stripe
        if (str_starts_with($transactionId, 'stripe_')) {
            return PaymentStatus::COMPLETED;
        }
        
        return PaymentStatus::FAILED;
    }
}

