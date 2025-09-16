<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Price
{
    private readonly int $amountInCents;
    private readonly string $currency;

    public function __construct(int $amountInCents, string $currency = 'EUR')
    {
        $this->validate($amountInCents, $currency);
        $this->amountInCents = $amountInCents;
        $this->currency = strtoupper($currency);
    }

    public static function fromEuros(float $euros, string $currency = 'EUR'): self
    {
        return new self((int) round($euros * 100), $currency);
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function getAmountInEuros(): float
    {
        return $this->amountInCents / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Price $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function subtract(Price $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amountInCents * $factor), $this->currency);
    }

    public function equals(Price $other): bool
    {
        return $this->amountInCents === $other->amountInCents && 
               $this->currency === $other->currency;
    }

    public function isGreaterThan(Price $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amountInCents > $other->amountInCents;
    }

    public function isZero(): bool
    {
        return $this->amountInCents === 0;
    }

    public function __toString(): string
    {
        return number_format($this->getAmountInEuros(), 2) . ' ' . $this->currency;
    }

    private function validate(int $amountInCents, string $currency): void
    {
        if ($amountInCents < 0) {
            throw new InvalidArgumentException('Il prezzo non può essere negativo');
        }

        if (empty($currency)) {
            throw new InvalidArgumentException('La valuta non può essere vuota');
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('La valuta deve essere un codice a 3 lettere');
        }
    }

    private function ensureSameCurrency(Price $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Impossibile operare con valute diverse: {$this->currency} e {$other->currency}"
            );
        }
    }
}
