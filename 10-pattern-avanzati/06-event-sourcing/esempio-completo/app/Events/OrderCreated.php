<?php

namespace App\Events;

class OrderCreated
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly array $items,
        public readonly float $totalAmount,
        public readonly string $shippingAddress,
        public readonly \DateTimeImmutable $createdAt
    ) {}
}
