<?php

namespace App\Events;

class OrderRefunded
{
    public function __construct(
        public readonly string $orderId,
        public readonly float $refundAmount,
        public readonly string $refundReason,
        public readonly \DateTimeImmutable $refundedAt
    ) {}
}
