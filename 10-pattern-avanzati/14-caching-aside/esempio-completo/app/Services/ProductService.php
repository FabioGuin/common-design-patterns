<?php

namespace App\Services;

use App\Cache\CacheManager;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private CacheManager $cacheManager
    ) {}

    public function getProduct(int $productId): ?array
    {
        return $this->cacheManager->get(
            (string) $productId,
            'products',
            function () use ($productId) {
                return $this->loadProductFromDatabase($productId);
            }
        );
    }

    public function getAllProducts(): array
    {
        return $this->cacheManager->get(
            'all_products',
            'products',
            function () {
                return $this->loadAllProductsFromDatabase();
            }
        );
    }

    public function getProductsByCategory(string $category): array
    {
        return $this->cacheManager->get(
            "products_category_{$category}",
            'products',
            function () use ($category) {
                return $this->loadProductsByCategoryFromDatabase($category);
            }
        );
    }

    public function createProduct(array $productData): array
    {
        $product = Product::create($productData);
        
        // Invalida cache correlata
        $this->invalidateRelatedCache();
        
        return $product->toArray();
    }

    public function updateProduct(int $productId, array $productData): array
    {
        $product = Product::findOrFail($productId);
        $product->update($productData);
        
        // Invalida cache specifica
        $this->cacheManager->forget((string) $productId, 'products');
        $this->invalidateRelatedCache();
        
        return $product->toArray();
    }

    public function deleteProduct(int $productId): bool
    {
        $product = Product::findOrFail($productId);
        $result = $product->delete();
        
        // Invalida cache
        $this->cacheManager->forget((string) $productId, 'products');
        $this->invalidateRelatedCache();
        
        return $result;
    }

    public function refreshProduct(int $productId): ?array
    {
        return $this->cacheManager->refresh(
            (string) $productId,
            'products',
            function () use ($productId) {
                return $this->loadProductFromDatabase($productId);
            }
        );
    }

    public function preloadProducts(): array
    {
        return $this->cacheManager->preload('products', function () {
            return $this->loadAllProductsFromDatabase();
        });
    }

    private function loadProductFromDatabase(int $productId): ?array
    {
        $product = Product::find($productId);
        return $product ? $product->toArray() : null;
    }

    private function loadAllProductsFromDatabase(): array
    {
        return Product::all()->toArray();
    }

    private function loadProductsByCategoryFromDatabase(string $category): array
    {
        return Product::where('category', $category)->get()->toArray();
    }

    private function invalidateRelatedCache(): void
    {
        // Invalida cache per tutte le categorie
        $categories = Product::distinct()->pluck('category');
        foreach ($categories as $category) {
            $this->cacheManager->forget("products_category_{$category}", 'products');
        }
        
        // Invalida cache per tutti i prodotti
        $this->cacheManager->forget('all_products', 'products');
    }

    public function getCacheStats(): array
    {
        return $this->cacheManager->getCacheStats('products');
    }
}
