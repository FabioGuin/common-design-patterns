<?php

namespace App\Events;

class OrderShipped
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $trackingNumber,
        public readonly string $carrier,
        public readonly \DateTimeImmutable $shippedAt
    ) {}
}
