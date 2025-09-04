<?php

namespace App\Services\Payment\Loggers;

use Illuminate\Support\Facades\Log;

class PayPalLogger implements PaymentLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $context['provider'] = 'paypal';
        Log::channel('payment')->{$level}($message, $context);
    }
    
    public function logPaymentStart(array $data): void
    {
        $this->log('info', 'PayPal payment started', [
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'paypal_order_id' => $data['paypal_order_id'] ?? null
        ]);
    }
    
    public function logPaymentComplete(string $transactionId, bool $success): void
    {
        $this->log($success ? 'info' : 'error', 'PayPal payment completed', [
            'transaction_id' => $transactionId,
            'success' => $success
        ]);
    }
    
    public function logPaymentError(string $error, array $context = []): void
    {
        $this->log('error', 'PayPal payment error: ' . $error, $context);
    }
}

