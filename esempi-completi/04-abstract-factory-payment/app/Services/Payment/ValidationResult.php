<?php

namespace App\Services\Payment;

class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors = []
    ) {}
    
    public static function valid(): self
    {
        return new self(true);
    }
    
    public static function invalid(array $errors): self
    {
        return new self(false, $errors);
    }
}

