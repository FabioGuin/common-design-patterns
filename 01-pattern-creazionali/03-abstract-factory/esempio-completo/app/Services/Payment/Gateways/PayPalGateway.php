<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\PaymentResult;
use App\Services\Payment\PaymentStatus;

class PayPalGateway implements PaymentGateway
{
    public function __construct(
        private string $clientId,
        private string $clientSecret
    ) {}
    
    public function processPayment(float $amount, array $data): PaymentResult
    {
        // Simulazione chiamata API PayPal
        $transactionId = 'paypal_' . uniqid();
        
        // Logica di pagamento PayPal
        if ($amount > 0 && isset($data['paypal_order_id'])) {
            return PaymentResult::success(
                $transactionId,
                'Payment processed successfully via PayPal',
                ['provider' => 'paypal', 'amount' => $amount]
            );
        }
        
        return PaymentResult::failure('Invalid payment data for PayPal');
    }
    
    public function refundPayment(string $transactionId, float $amount): PaymentResult
    {
        // Simulazione rimborso PayPal
        if (str_starts_with($transactionId, 'paypal_')) {
            return PaymentResult::success(
                $transactionId,
                'Refund processed successfully via PayPal',
                ['refund_amount' => $amount]
            );
        }
        
        return PaymentResult::failure('Invalid transaction ID for PayPal');
    }
    
    public function getPaymentStatus(string $transactionId): PaymentStatus
    {
        // Simulazione verifica stato PayPal
        if (str_starts_with($transactionId, 'paypal_')) {
            return PaymentStatus::COMPLETED;
        }
        
        return PaymentStatus::FAILED;
    }
}

