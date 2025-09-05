<?php

namespace App\Events;

class OrderDelivered
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $deliveryConfirmation,
        public readonly \DateTimeImmutable $deliveredAt
    ) {}
}
