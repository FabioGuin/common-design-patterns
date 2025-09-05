<?php

namespace App\Services;

use App\Models\Order;

interface PaymentServiceInterface
{
    public function processPayment(Order $order, array $paymentData): bool;
    public function refundPayment(Order $order, float $amount = null): bool;
    public function getPaymentStatus(Order $order): string;
}

class PaymentService implements PaymentServiceInterface
{
    private string $gatewayUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->gatewayUrl = config('services.payment.gateway_url');
        $this->apiKey = config('services.payment.api_key');
    }

    public function processPayment(Order $order, array $paymentData): bool
    {
        // Simula chiamata al gateway di pagamento
        $response = $this->callPaymentGateway('charge', [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'currency' => 'EUR',
            'payment_method' => $paymentData['method'],
            'card_token' => $paymentData['card_token'] ?? null
        ]);

        if ($response['success']) {
            $order->markAsPaid();
            return true;
        }

        return false;
    }

    public function refundPayment(Order $order, float $amount = null): bool
    {
        $refundAmount = $amount ?? $order->total_amount;

        $response = $this->callPaymentGateway('refund', [
            'order_id' => $order->id,
            'amount' => $refundAmount,
            'reason' => 'customer_request'
        ]);

        return $response['success'] ?? false;
    }

    public function getPaymentStatus(Order $order): string
    {
        $response = $this->callPaymentGateway('status', [
            'order_id' => $order->id
        ]);

        return $response['status'] ?? 'unknown';
    }

    private function callPaymentGateway(string $endpoint, array $data): array
    {
        // Simula chiamata HTTP al gateway di pagamento
        // In un'applicazione reale, useresti Guzzle o HTTP Client di Laravel
        
        $url = $this->gatewayUrl . '/' . $endpoint;
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ];

        // Simula risposta del gateway
        return [
            'success' => true,
            'status' => 'completed',
            'transaction_id' => 'txn_' . uniqid(),
            'amount' => $data['amount'] ?? 0
        ];
    }
}
