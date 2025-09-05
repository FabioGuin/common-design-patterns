<?php

namespace App\Commands;

class CreateProductCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly string $category,
        public readonly array $attributes = []
    ) {}
}
