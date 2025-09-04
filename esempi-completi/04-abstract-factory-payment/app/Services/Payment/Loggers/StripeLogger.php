<?php

namespace App\Services\Payment\Loggers;

use Illuminate\Support\Facades\Log;

class StripeLogger implements PaymentLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $context['provider'] = 'stripe';
        Log::channel('payment')->{$level}($message, $context);
    }
    
    public function logPaymentStart(array $data): void
    {
        $this->log('info', 'Stripe payment started', [
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null
        ]);
    }
    
    public function logPaymentComplete(string $transactionId, bool $success): void
    {
        $this->log($success ? 'info' : 'error', 'Stripe payment completed', [
            'transaction_id' => $transactionId,
            'success' => $success
        ]);
    }
    
    public function logPaymentError(string $error, array $context = []): void
    {
        $this->log('error', 'Stripe payment error: ' . $error, $context);
    }
}

