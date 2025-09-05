<?php

namespace App\Events;

class OrderCancelled
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
        public readonly \DateTimeImmutable $cancelledAt
    ) {}
}
