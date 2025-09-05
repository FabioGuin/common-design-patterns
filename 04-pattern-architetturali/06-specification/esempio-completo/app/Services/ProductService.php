<?php

namespace App\Services;

use App\Models\Product;
use App\Specifications\Interfaces\SpecificationInterface;
use App\Specifications\Product\PriceRangeSpecification;
use App\Specifications\Product\InStockSpecification;
use App\Specifications\Product\CategorySpecification;
use App\Specifications\Product\NameContainsSpecification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * Recupera prodotti usando una specifica
     */
    public function getProducts(SpecificationInterface $specification): Collection
    {
        $cacheKey = 'products_' . $specification->getHash();
        
        return Cache::remember($cacheKey, 300, function () use ($specification) {
            return $specification->toQuery()->with('category')->get();
        });
    }

    /**
     * Recupera prodotti disponibili
     */
    public function getAvailableProducts(): Collection
    {
        $specification = new InStockSpecification();
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti in un range di prezzo
     */
    public function getProductsInPriceRange(float $minPrice, float $maxPrice): Collection
    {
        $specification = new PriceRangeSpecification($minPrice, $maxPrice);
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti per categoria
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        $specification = new CategorySpecification($categoryId);
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti che contengono un termine nel nome
     */
    public function searchProductsByName(string $searchTerm): Collection
    {
        $specification = new NameContainsSpecification($searchTerm);
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti in categoria e range di prezzo
     */
    public function getProductsInCategoryAndPriceRange(
        int $categoryId, 
        float $minPrice, 
        float $maxPrice
    ): Collection {
        $categorySpec = new CategorySpecification($categoryId);
        $priceSpec = new PriceRangeSpecification($minPrice, $maxPrice);
        $specification = $categorySpec->and($priceSpec);
        
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti disponibili in un range di prezzo
     */
    public function getAvailableProductsInPriceRange(
        float $minPrice, 
        float $maxPrice
    ): Collection {
        $inStockSpec = new InStockSpecification();
        $priceSpec = new PriceRangeSpecification($minPrice, $maxPrice);
        $specification = $inStockSpec->and($priceSpec);
        
        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti per categoria e disponibilità
     */
    public function getAvailableProductsByCategory(int $categoryId): Collection
    {
        $categorySpec = new CategorySpecification($categoryId);
        $inStockSpec = new InStockSpecification();
        $specification = $categorySpec->and($inStockSpec);
        
        return $this->getProducts($specification);
    }

    /**
     * Cerca prodotti con criteri multipli
     */
    public function searchProducts(array $criteria): Collection
    {
        $specifications = [];

        if (isset($criteria['name'])) {
            $specifications[] = new NameContainsSpecification($criteria['name']);
        }

        if (isset($criteria['category_id'])) {
            $specifications[] = new CategorySpecification($criteria['category_id']);
        }

        if (isset($criteria['min_price']) || isset($criteria['max_price'])) {
            $minPrice = $criteria['min_price'] ?? 0;
            $maxPrice = $criteria['max_price'] ?? PHP_FLOAT_MAX;
            $specifications[] = new PriceRangeSpecification($minPrice, $maxPrice);
        }

        if (isset($criteria['in_stock']) && $criteria['in_stock']) {
            $specifications[] = new InStockSpecification();
        }

        if (empty($specifications)) {
            return Product::with('category')->get();
        }

        $specification = array_shift($specifications);
        foreach ($specifications as $spec) {
            $specification = $specification->and($spec);
        }

        return $this->getProducts($specification);
    }

    /**
     * Recupera prodotti popolari
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        $specification = new InStockSpecification();
        
        return $specification->toQuery()
            ->with('category')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupera prodotti recenti
     */
    public function getRecentProducts(int $limit = 10): Collection
    {
        $specification = new InStockSpecification();
        
        return $specification->toQuery()
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupera prodotti in offerta
     */
    public function getProductsOnSale(): Collection
    {
        $inStockSpec = new InStockSpecification();
        $saleSpec = new PriceRangeSpecification(0, 100); // Esempio: prodotti sotto 100€
        $specification = $inStockSpec->and($saleSpec);
        
        return $this->getProducts($specification);
    }

    /**
     * Recupera statistiche dei prodotti
     */
    public function getProductStatistics(): array
    {
        return [
            'total_products' => Product::count(),
            'available_products' => (new InStockSpecification())->toQuery()->count(),
            'products_on_sale' => $this->getProductsOnSale()->count(),
            'average_price' => Product::avg('price'),
            'total_value' => Product::sum('price'),
        ];
    }

    /**
     * Verifica se un prodotto soddisfa una specifica
     */
    public function productSatisfiesSpecification(Product $product, SpecificationInterface $specification): bool
    {
        return $specification->isSatisfiedBy($product);
    }

    /**
     * Filtra una collezione di prodotti usando una specifica
     */
    public function filterProducts(Collection $products, SpecificationInterface $specification): Collection
    {
        return $products->filter(function ($product) use ($specification) {
            return $specification->isSatisfiedBy($product);
        });
    }

    /**
     * Ottiene prodotti consigliati per un prodotto
     */
    public function getRecommendedProducts(Product $product, int $limit = 5): Collection
    {
        $categorySpec = new CategorySpecification($product->category_id);
        $inStockSpec = new InStockSpecification();
        $notCurrentSpec = new \App\Specifications\Product\NotProductSpecification($product->id);
        
        $specification = $categorySpec->and($inStockSpec)->and($notCurrentSpec);
        
        return $specification->toQuery()
            ->with('category')
            ->limit($limit)
            ->get();
    }

    /**
     * Pulisce la cache dei prodotti
     */
    public function clearProductCache(): void
    {
        Cache::forget('products_*');
    }
}
