<?php

namespace App\Services;

use App\Retry\RetryManager;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private RetryManager $retryManager
    ) {}

    public function processPayment(array $paymentData): array
    {
        return $this->retryManager->execute('payment_service', function () use ($paymentData) {
            return $this->callExternalPaymentService($paymentData);
        });
    }

    public function refundPayment(string $transactionId): array
    {
        return $this->retryManager->execute('payment_service', function () use ($transactionId) {
            return $this->callExternalRefundService($transactionId);
        });
    }

    public function validatePaymentMethod(string $paymentMethod): array
    {
        return $this->retryManager->execute('payment_service', function () use ($paymentMethod) {
            return $this->callExternalValidationService($paymentMethod);
        });
    }

    private function callExternalPaymentService(array $paymentData): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 3) === 1) {
            throw new \Exception("Payment service temporarily unavailable", 503);
        }

        return [
            'transaction_id' => Str::uuid()->toString(),
            'status' => 'completed',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'processed_at' => now()->toISOString(),
            'priority' => 'high',
        ];
    }

    private function callExternalRefundService(string $transactionId): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 4) === 1) {
            throw new \Exception("Refund service temporarily unavailable", 502);
        }

        return [
            'refund_id' => Str::uuid()->toString(),
            'transaction_id' => $transactionId,
            'status' => 'refunded',
            'refunded_at' => now()->toISOString(),
            'priority' => 'high',
        ];
    }

    private function callExternalValidationService(string $paymentMethod): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 5) === 1) {
            throw new \Exception("Validation service temporarily unavailable", 500);
        }

        return [
            'payment_method' => $paymentMethod,
            'is_valid' => true,
            'validated_at' => now()->toISOString(),
            'priority' => 'high',
        ];
    }

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete
        usleep(rand(100000, 500000)); // 100-500ms
    }

    public function getServiceStatus(): array
    {
        return $this->retryManager->getRetryStatus('payment_service') ?? [
            'service_name' => 'payment_service',
            'status' => 'UNKNOWN',
            'message' => 'Retry not initialized'
        ];
    }
}
