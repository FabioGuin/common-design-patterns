<?php

namespace App\Commands;

class CreateOrderCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly array $items, // [['product_id' => 1, 'quantity' => 2], ...]
        public readonly string $shippingAddress,
        public readonly string $billingAddress
    ) {}
}
