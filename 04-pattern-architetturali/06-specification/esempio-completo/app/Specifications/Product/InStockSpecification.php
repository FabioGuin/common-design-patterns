<?php

namespace App\Specifications\Product;

use App\Models\Product;
use App\Specifications\Base\BaseSpecification;
use Illuminate\Database\Eloquent\Builder;

class InStockSpecification extends BaseSpecification
{
    public function __construct(
        private int $minStock = 1
    ) {
        if ($minStock < 0) {
            throw new \InvalidArgumentException('Min stock cannot be negative');
        }
    }

    /**
     * Verifica se il prodotto è in stock
     */
    public function isSatisfiedBy($product): bool
    {
        if (!$product instanceof Product) {
            return false;
        }

        return $product->stock >= $this->minStock;
    }

    /**
     * Converte la specifica in una query builder
     */
    public function toQuery(): Builder
    {
        return Product::query()->where('stock', '>=', $this->minStock);
    }

    /**
     * Ottiene una descrizione della specifica
     */
    public function getDescription(): string
    {
        return $this->minStock === 1 
            ? 'In stock' 
            : "Stock >= {$this->minStock}";
    }

    /**
     * Ottiene i dati serializzabili della specifica
     */
    protected function getSerializableData(): array
    {
        return [
            'minStock' => $this->minStock
        ];
    }

    /**
     * Ottiene il stock minimo richiesto
     */
    public function getMinStock(): int
    {
        return $this->minStock;
    }

    /**
     * Verifica se un prodotto ha stock sufficiente
     */
    public function hasEnoughStock(Product $product): bool
    {
        return $product->stock >= $this->minStock;
    }

    /**
     * Ottiene la quantità di stock disponibile
     */
    public function getAvailableStock(Product $product): int
    {
        return max(0, $product->stock - $this->minStock + 1);
    }
}
