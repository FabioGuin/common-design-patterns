<?php

namespace App\ValueObjects;

/**
 * Value Object per rappresentare SKU prodotti
 * 
 * Immutabile, validato e type-safe per identificatori prodotti.
 */
class ProductSku extends ValueObject
{
    private readonly string $value;

    private const PATTERN = '/^[A-Z0-9]{3,}-[A-Z0-9]{3,}-[A-Z0-9]{3,}$/';
    private const MIN_LENGTH = 9; // 3-3-3 + 2 separatori
    private const MAX_LENGTH = 50;

    private function __construct(string $sku)
    {
        $this->validateInput($sku);
        $this->value = strtoupper(trim($sku));
    }

    /**
     * Crea un nuovo ProductSku
     */
    public static function create(string $sku): self
    {
        return new self($sku);
    }

    /**
     * Genera un SKU automaticamente
     */
    public static function generate(string $category, string $productName): self
    {
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $category), 0, 3));
        $productCode = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $productName), 0, 3));
        $randomCode = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));
        
        $sku = $categoryCode . '-' . $productCode . '-' . $randomCode;
        
        return new self($sku);
    }

    /**
     * Restituisce il valore dello SKU
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Restituisce il valore come stringa
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Restituisce le parti dello SKU
     */
    public function getParts(): array
    {
        return explode('-', $this->value);
    }

    /**
     * Restituisce il codice categoria
     */
    public function getCategoryCode(): string
    {
        $parts = $this->getParts();
        return $parts[0] ?? '';
    }

    /**
     * Restituisce il codice prodotto
     */
    public function getProductCode(): string
    {
        $parts = $this->getParts();
        return $parts[1] ?? '';
    }

    /**
     * Restituisce il codice random
     */
    public function getRandomCode(): string
    {
        $parts = $this->getParts();
        return $parts[2] ?? '';
    }

    /**
     * Verifica se lo SKU appartiene a una categoria
     */
    public function belongsToCategory(string $category): bool
    {
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $category), 0, 3));
        return $this->getCategoryCode() === $categoryCode;
    }

    /**
     * Verifica se lo SKU è valido
     */
    public function isValid(): bool
    {
        return preg_match(self::PATTERN, $this->value) === 1;
    }

    /**
     * Restituisce una rappresentazione stringa
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Serializza per JSON
     */
    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->value,
            'parts' => $this->getParts(),
            'categoryCode' => $this->getCategoryCode(),
            'productCode' => $this->getProductCode(),
            'randomCode' => $this->getRandomCode(),
            'isValid' => $this->isValid()
        ];
    }

    /**
     * Valida l'input
     */
    private function validateInput(string $sku): void
    {
        $sku = trim($sku);

        if (empty($sku)) {
            throw new \InvalidArgumentException('SKU cannot be empty');
        }

        if (strlen($sku) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException('SKU is too short');
        }

        if (strlen($sku) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('SKU is too long');
        }

        if (preg_match(self::PATTERN, $sku) !== 1) {
            throw new \InvalidArgumentException('SKU format is invalid. Expected format: XXX-XXX-XXX');
        }
    }
}
