<?php

namespace App\Events;

class ProductUpdated
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly string $category,
        public readonly array $attributes
    ) {}
}
