<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    public function processPayment(array $orderData): array
    {
        $amount = $orderData['total_amount'];
        $paymentMethod = $orderData['payment_method'] ?? 'credit_card';

        // Simula validazione pagamento
        if ($amount <= 0) {
            throw new \Exception("Invalid payment amount: {$amount}");
        }

        // Simula fallimento casuale per testing
        if (rand(1, 10) === 1) {
            throw new \Exception("Payment processing failed - insufficient funds");
        }

        // Crea transazione
        $payment = Payment::create([
            'transaction_id' => Str::uuid()->toString(),
            'order_id' => $orderData['order_id'],
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        return [
            'transaction_id' => $payment->transaction_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'completed'
        ];
    }

    public function refundPayment(string $transactionId): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();
        
        if (!$payment) {
            throw new \Exception("Payment not found: {$transactionId}");
        }

        if ($payment->status !== 'completed') {
            throw new \Exception("Payment not completed, cannot refund: {$transactionId}");
        }

        // Simula rimborso
        $payment->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        return [
            'transaction_id' => $transactionId,
            'refunded_amount' => $payment->amount,
            'status' => 'refunded'
        ];
    }

    public function getPayment(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    public function getAllPayments(): array
    {
        return Payment::orderBy('processed_at', 'desc')->get()->toArray();
    }

    public function getPaymentsByOrder(string $orderId): array
    {
        return Payment::where('order_id', $orderId)->get()->toArray();
    }
}
