<?php

namespace App\Commands;

class UpdateProductCommand
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?float $price = null,
        public readonly ?int $stock = null,
        public readonly ?string $category = null,
        public readonly ?array $attributes = null
    ) {}
}
