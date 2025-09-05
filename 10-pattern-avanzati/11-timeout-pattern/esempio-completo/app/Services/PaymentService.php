<?php

namespace App\Services;

use App\Timeout\TimeoutManager;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private TimeoutManager $timeoutManager
    ) {}

    public function processPayment(array $paymentData): array
    {
        return $this->timeoutManager->execute('payment_service', function () use ($paymentData) {
            return $this->callExternalPaymentService($paymentData);
        });
    }

    public function refundPayment(string $transactionId): array
    {
        return $this->timeoutManager->execute('payment_service', function () use ($transactionId) {
            return $this->callExternalRefundService($transactionId);
        });
    }

    public function validatePaymentMethod(string $paymentMethod): array
    {
        return $this->timeoutManager->execute('payment_service', function () use ($paymentMethod) {
            return $this->callExternalValidationService($paymentMethod);
        });
    }

    private function callExternalPaymentService(array $paymentData): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula operazione lenta per testing
        if (rand(1, 5) === 1) {
            $this->simulateSlowOperation();
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
        
        // Simula operazione lenta per testing
        if (rand(1, 6) === 1) {
            $this->simulateSlowOperation();
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
        
        // Simula operazione lenta per testing
        if (rand(1, 7) === 1) {
            $this->simulateSlowOperation();
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
        // Simula latenza di rete normale
        usleep(rand(100000, 500000)); // 100-500ms
    }

    private function simulateSlowOperation(): void
    {
        // Simula operazione lenta che causerà timeout
        usleep(rand(20000000, 30000000)); // 20-30 secondi
    }

    public function getServiceStatus(): array
    {
        return $this->timeoutManager->getTimeoutStatus('payment_service') ?? [
            'service_name' => 'payment_service',
            'status' => 'UNKNOWN',
            'message' => 'Timeout not initialized'
        ];
    }
}
