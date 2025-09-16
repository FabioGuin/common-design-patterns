<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Email
{
    private readonly string $value;

    public function __construct(string $email)
    {
        $this->validate($email);
        $this->value = strtolower(trim($email));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email non può essere vuota');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email non valida: {$email}");
        }

        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email troppo lunga');
        }
    }
}
