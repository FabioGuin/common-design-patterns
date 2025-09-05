<?php

namespace App\Events;

class OrderPaid
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $paymentMethod,
        public readonly string $transactionId,
        public readonly \DateTimeImmutable $paidAt
    ) {}
}
