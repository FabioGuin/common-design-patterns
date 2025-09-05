<?php

namespace App\Entities;

/**
 * OrderItem Entity
 * 
 * Rappresenta un item all'interno di un ordine.
 * Non può essere modificata direttamente dall'esterno,
 * solo tramite l'Aggregate Root.
 */
class OrderItem
{
    private const MAX_QUANTITY_PER_ITEM = 100;

    private string $productId;
    private int $quantity;
    private float $price;

    public function __construct(string $productId, int $quantity, float $price)
    {
        $this->validateInput($productId, $quantity, $price);
        
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    /**
     * Aggiorna la quantità dell'item
     */
    public function updateQuantity(int $quantity): void
    {
        $this->validateQuantity($quantity);
        $this->quantity = $quantity;
    }

    /**
     * Restituisce l'ID del prodotto
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * Restituisce la quantità
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Restituisce il prezzo
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Restituisce il totale dell'item
     */
    public function getTotal(): float
    {
        return $this->quantity * $this->price;
    }

    /**
     * Restituisce una rappresentazione array
     */
    public function toArray(): array
    {
        return [
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->getTotal()
        ];
    }

    /**
     * Valida l'input
     */
    private function validateInput(string $productId, int $quantity, float $price): void
    {
        if (empty($productId)) {
            throw new \InvalidArgumentException('Product ID cannot be empty');
        }

        $this->validateQuantity($quantity);

        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        if ($price > 10000) {
            throw new \InvalidArgumentException('Price cannot exceed 10000');
        }
    }

    /**
     * Valida la quantità
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        if ($quantity > self::MAX_QUANTITY_PER_ITEM) {
            throw new \InvalidArgumentException("Quantity cannot exceed " . self::MAX_QUANTITY_PER_ITEM);
        }
    }
}
