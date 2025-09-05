<?php

namespace App\Events;

class OrderCreated
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly array $items,
        public readonly float $totalAmount,
        public readonly string $shippingAddress,
        public readonly string $billingAddress
    ) {}
}
