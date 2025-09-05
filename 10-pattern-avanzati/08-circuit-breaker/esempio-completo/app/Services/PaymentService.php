<?php

namespace App\Services;

use App\CircuitBreaker\CircuitBreakerManager;
use App\Fallbacks\PaymentFallback;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private CircuitBreakerManager $circuitBreakerManager,
        private PaymentFallback $paymentFallback
    ) {}

    public function processPayment(array $paymentData): array
    {
        return $this->circuitBreakerManager->call(
            'payment_service',
            function () use ($paymentData) {
                return $this->callExternalPaymentService($paymentData);
            },
            function () use ($paymentData) {
                return $this->paymentFallback->processPayment($paymentData);
            }
        );
    }

    public function refundPayment(string $transactionId): array
    {
        return $this->circuitBreakerManager->call(
            'payment_service',
            function () use ($transactionId) {
                return $this->callExternalRefundService($transactionId);
            },
            function () use ($transactionId) {
                return $this->paymentFallback->refundPayment($transactionId);
            }
        );
    }

    private function callExternalPaymentService(array $paymentData): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 10) === 1) {
            throw new \Exception("Payment service temporarily unavailable");
        }

        return [
            'transaction_id' => Str::uuid()->toString(),
            'status' => 'completed',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'processed_at' => now()->toISOString(),
        ];
    }

    private function callExternalRefundService(string $transactionId): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 15) === 1) {
            throw new \Exception("Refund service temporarily unavailable");
        }

        return [
            'refund_id' => Str::uuid()->toString(),
            'transaction_id' => $transactionId,
            'status' => 'refunded',
            'refunded_at' => now()->toISOString(),
        ];
    }

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete
        usleep(rand(100000, 500000)); // 100-500ms
    }

    public function getServiceStatus(): array
    {
        return $this->circuitBreakerManager->getCircuitBreakerState('payment_service') ?? [
            'service_name' => 'payment_service',
            'state' => 'UNKNOWN',
            'message' => 'Circuit breaker not initialized'
        ];
    }
}
