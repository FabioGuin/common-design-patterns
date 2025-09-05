<?php

namespace App\Fallbacks;

use Illuminate\Support\Str;

class PaymentFallback
{
    public function processPayment(array $paymentData): array
    {
        // Simula elaborazione offline
        usleep(100000); // 100ms

        return [
            'transaction_id' => Str::uuid()->toString(),
            'status' => 'pending_offline',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'processed_at' => now()->toISOString(),
            'fallback_reason' => 'Payment service unavailable - processed offline',
            'requires_manual_review' => true,
        ];
    }

    public function refundPayment(string $transactionId): array
    {
        // Simula rimborso offline
        usleep(100000); // 100ms

        return [
            'refund_id' => Str::uuid()->toString(),
            'transaction_id' => $transactionId,
            'status' => 'pending_offline',
            'refunded_at' => now()->toISOString(),
            'fallback_reason' => 'Refund service unavailable - processed offline',
            'requires_manual_review' => true,
        ];
    }
}
