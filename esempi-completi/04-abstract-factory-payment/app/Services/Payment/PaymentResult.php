<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $message,
        public readonly array $data = []
    ) {}
    
    public static function success(string $transactionId, string $message, array $data = []): self
    {
        return new self(true, $transactionId, $message, $data);
    }
    
    public static function failure(string $message, array $data = []): self
    {
        return new self(false, '', $message, $data);
    }
}

