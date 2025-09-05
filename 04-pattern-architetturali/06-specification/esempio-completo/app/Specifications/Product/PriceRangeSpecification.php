<?php

namespace App\Specifications\Product;

use App\Models\Product;
use App\Specifications\Base\BaseSpecification;
use Illuminate\Database\Eloquent\Builder;

class PriceRangeSpecification extends BaseSpecification
{
    public function __construct(
        private float $minPrice,
        private float $maxPrice
    ) {
        if ($minPrice > $maxPrice) {
            throw new \InvalidArgumentException('Min price cannot be greater than max price');
        }
    }

    /**
     * Verifica se il prodotto soddisfa la specifica di prezzo
     */
    public function isSatisfiedBy($product): bool
    {
        if (!$product instanceof Product) {
            return false;
        }

        return $product->price >= $this->minPrice && $product->price <= $this->maxPrice;
    }

    /**
     * Converte la specifica in una query builder
     */
    public function toQuery(): Builder
    {
        return Product::query()->whereBetween('price', [$this->minPrice, $this->maxPrice]);
    }

    /**
     * Ottiene una descrizione della specifica
     */
    public function getDescription(): string
    {
        return "Price between {$this->minPrice} and {$this->maxPrice}";
    }

    /**
     * Ottiene i dati serializzabili della specifica
     */
    protected function getSerializableData(): array
    {
        return [
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice
        ];
    }

    /**
     * Ottiene il prezzo minimo
     */
    public function getMinPrice(): float
    {
        return $this->minPrice;
    }

    /**
     * Ottiene il prezzo massimo
     */
    public function getMaxPrice(): float
    {
        return $this->maxPrice;
    }

    /**
     * Verifica se un prezzo è nel range
     */
    public function isPriceInRange(float $price): bool
    {
        return $price >= $this->minPrice && $price <= $this->maxPrice;
    }

    /**
     * Ottiene il range di prezzo come array
     */
    public function getPriceRange(): array
    {
        return [
            'min' => $this->minPrice,
            'max' => $this->maxPrice
        ];
    }
}
