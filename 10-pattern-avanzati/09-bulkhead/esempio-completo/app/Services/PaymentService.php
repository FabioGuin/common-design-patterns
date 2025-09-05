<?php

namespace App\Services;

use App\Bulkhead\BulkheadManager;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private BulkheadManager $bulkheadManager
    ) {}

    public function processPayment(array $paymentData): array
    {
        return $this->bulkheadManager->execute('payment_service', function () use ($paymentData) {
            return $this->performPayment($paymentData);
        });
    }

    public function refundPayment(string $transactionId): array
    {
        return $this->bulkheadManager->execute('payment_service', function () use ($transactionId) {
            return $this->performRefund($transactionId);
        });
    }

    public function validatePaymentMethod(string $paymentMethod): array
    {
        return $this->bulkheadManager->execute('payment_service', function () use ($paymentMethod) {
            return $this->performValidation($paymentMethod);
        });
    }

    private function performPayment(array $paymentData): array
    {
        // Simula elaborazione pagamento critica
        $this->simulateCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 20) === 1) {
            throw new \Exception("Payment processing failed");
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

    private function performRefund(string $transactionId): array
    {
        // Simula elaborazione rimborso critica
        $this->simulateCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 25) === 1) {
            throw new \Exception("Refund processing failed");
        }

        return [
            'refund_id' => Str::uuid()->toString(),
            'transaction_id' => $transactionId,
            'status' => 'refunded',
            'refunded_at' => now()->toISOString(),
            'priority' => 'high',
        ];
    }

    private function performValidation(string $paymentMethod): array
    {
        // Simula validazione pagamento critica
        $this->simulateCriticalOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 30) === 1) {
            throw new \Exception("Payment method validation failed");
        }

        return [
            'payment_method' => $paymentMethod,
            'is_valid' => true,
            'validated_at' => now()->toISOString(),
            'priority' => 'high',
        ];
    }

    private function simulateCriticalOperation(): void
    {
        // Simula operazione critica con alta priorità
        usleep(rand(50000, 200000)); // 50-200ms
    }

    public function getServiceStatus(): array
    {
        return $this->bulkheadManager->getBulkheadStatus('payment_service') ?? [
            'service_name' => 'payment_service',
            'status' => 'UNKNOWN',
            'message' => 'Bulkhead not initialized'
        ];
    }
}
