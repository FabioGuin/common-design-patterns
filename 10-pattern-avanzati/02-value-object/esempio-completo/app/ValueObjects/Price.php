<?php

namespace App\ValueObjects;

/**
 * Value Object per rappresentare prezzi con valute
 * 
 * Immutabile, validato e type-safe per operazioni finanziarie.
 */
class Price extends ValueObject
{
    private readonly int $cents;
    private readonly string $currency;

    private const SUPPORTED_CURRENCIES = ['EUR', 'USD', 'GBP', 'JPY'];
    private const CURRENCY_SYMBOLS = [
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'JPY' => '¥'
    ];

    private function __construct(int $cents, string $currency)
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            throw new \InvalidArgumentException("Unsupported currency: {$currency}");
        }

        $this->cents = $cents;
        $this->currency = $currency;
    }

    /**
     * Crea un nuovo Price
     */
    public static function create(int $cents, string $currency): self
    {
        return new self($cents, $currency);
    }

    /**
     * Crea un Price da un valore decimale
     */
    public static function fromDecimal(float $amount, string $currency): self
    {
        $cents = (int) round($amount * 100);
        return new self($cents, $currency);
    }

    /**
     * Restituisce il valore in centesimi
     */
    public function getCents(): int
    {
        return $this->cents;
    }

    /**
     * Restituisce la valuta
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Restituisce il valore decimale
     */
    public function getDecimal(): float
    {
        return $this->cents / 100;
    }

    /**
     * Restituisce il valore formattato
     */
    public function getFormatted(): string
    {
        $symbol = self::CURRENCY_SYMBOLS[$this->currency] ?? $this->currency;
        $amount = number_format($this->getDecimal(), 2);
        
        return $symbol . $amount;
    }

    /**
     * Somma due prezzi
     */
    public function add(Price $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add prices with different currencies');
        }

        return new self($this->cents + $other->cents, $this->currency);
    }

    /**
     * Sottrae un prezzo
     */
    public function subtract(Price $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract prices with different currencies');
        }

        $newCents = $this->cents - $other->cents;
        if ($newCents < 0) {
            throw new \InvalidArgumentException('Result cannot be negative');
        }

        return new self($newCents, $this->currency);
    }

    /**
     * Moltiplica per un fattore
     */
    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new \InvalidArgumentException('Factor cannot be negative');
        }

        $newCents = (int) round($this->cents * $factor);
        return new self($newCents, $this->currency);
    }

    /**
     * Confronta due prezzi
     */
    public function isGreaterThan(Price $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot compare prices with different currencies');
        }

        return $this->cents > $other->cents;
    }

    /**
     * Confronta due prezzi
     */
    public function isLessThan(Price $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot compare prices with different currencies');
        }

        return $this->cents < $other->cents;
    }

    /**
     * Verifica se il prezzo è zero
     */
    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    /**
     * Restituisce il valore primitivo per il confronto
     */
    public function getValue(): mixed
    {
        return [
            'cents' => $this->cents,
            'currency' => $this->currency
        ];
    }

    /**
     * Restituisce una rappresentazione stringa
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }

    /**
     * Serializza per JSON
     */
    public function jsonSerialize(): mixed
    {
        return [
            'amount' => $this->getDecimal(),
            'cents' => $this->cents,
            'currency' => $this->currency,
            'formatted' => $this->getFormatted()
        ];
    }
}
