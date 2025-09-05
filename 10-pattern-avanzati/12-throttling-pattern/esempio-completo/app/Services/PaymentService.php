<?php

namespace App\Services;

use App\Throttling\ThrottlingManager;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private ThrottlingManager $throttlingManager
    ) {}

    public function processPayment(array $paymentData, string $userId): array
    {
        return $this->throttlingManager->execute('payment_service', $userId, function () use ($paymentData) {
            return $this->callExternalPaymentService($paymentData);
        }, 'api/payment');
    }

    public function refundPayment(string $transactionId, string $userId): array
    {
        return $this->throttlingManager->execute('payment_service', $userId, function () use ($transactionId) {
            return $this->callExternalRefundService($transactionId);
        }, 'api/refund');
    }

    public function validatePaymentMethod(string $paymentMethod, string $userId): array
    {
        return $this->throttlingManager->execute('payment_service', $userId, function () use ($paymentMethod) {
            return $this->callExternalValidationService($paymentMethod);
        }, 'api/validation');
    }

    private function callExternalPaymentService(array $paymentData): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();

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

    public function getServiceStatus(string $userId): array
    {
        return $this->throttlingManager->getThrottlingStatus('payment_service', $userId, 'api/payment') ?? [
            'service_name' => 'payment_service',
            'status' => 'UNKNOWN',
            'message' => 'Throttling not initialized'
        ];
    }
}
