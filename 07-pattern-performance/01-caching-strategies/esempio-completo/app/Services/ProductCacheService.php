<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductCacheService
{
    private CacheService $cacheService;
    private array $cacheTags = ['products', 'categories'];

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get all products with caching
     */
    public function getAllProducts(): array
    {
        return $this->cacheService->rememberWithTags(
            'products:all',
            $this->cacheTags,
            function () {
                Log::info('Loading all products from database');
                return Product::with(['category', 'reviews'])->get()->toArray();
            }
        );
    }

    /**
     * Get product by ID with caching
     */
    public function getProductById(int $id): ?array
    {
        return $this->cacheService->rememberWithTags(
            "product:{$id}",
            $this->cacheTags,
            function () use ($id) {
                Log::info("Loading product {$id} from database");
                $product = Product::with(['category', 'reviews'])->find($id);
                return $product ? $product->toArray() : null;
            }
        );
    }

    /**
     * Get products by category with caching
     */
    public function getProductsByCategory(int $categoryId): array
    {
        return $this->cacheService->rememberWithTags(
            "products:category:{$categoryId}",
            $this->cacheTags,
            function () use ($categoryId) {
                Log::info("Loading products for category {$categoryId} from database");
                return Product::where('category_id', $categoryId)
                    ->with(['category', 'reviews'])
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Get featured products with caching
     */
    public function getFeaturedProducts(): array
    {
        return $this->cacheService->rememberWithTags(
            'products:featured',
            $this->cacheTags,
            function () {
                Log::info('Loading featured products from database');
                return Product::where('is_featured', true)
                    ->with(['category', 'reviews'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Search products with caching
     */
    public function searchProducts(string $query): array
    {
        $cacheKey = $this->cacheService->generateKey('products:search', ['query' => $query]);
        
        return $this->cacheService->rememberWithTags(
            $cacheKey,
            $this->cacheTags,
            function () use ($query) {
                Log::info("Searching products with query: {$query}");
                return Product::where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->with(['category', 'reviews'])
                    ->get()
                    ->toArray();
            }
        );
    }

    /**
     * Get product statistics with caching
     */
    public function getProductStats(): array
    {
        return $this->cacheService->rememberWithTags(
            'products:stats',
            $this->cacheTags,
            function () {
                Log::info('Loading product statistics from database');
                return [
                    'total_products' => Product::count(),
                    'featured_products' => Product::where('is_featured', true)->count(),
                    'average_price' => Product::avg('price'),
                    'total_categories' => Category::count(),
                    'last_updated' => now()->toISOString()
                ];
            }
        );
    }

    /**
     * Invalidate product cache
     */
    public function invalidateProductCache(int $productId = null): void
    {
        if ($productId) {
            $this->cacheService->forget("product:{$productId}");
        }
        
        $this->cacheService->forgetWithTags($this->cacheTags);
        Log::info('Product cache invalidated', ['product_id' => $productId]);
    }

    /**
     * Warm up cache
     */
    public function warmUpCache(): void
    {
        Log::info('Warming up product cache');
        
        $this->getAllProducts();
        $this->getFeaturedProducts();
        $this->getProductStats();
        
        // Warm up category cache
        Category::all()->each(function ($category) {
            $this->getProductsByCategory($category->id);
        });
        
        Log::info('Product cache warmed up successfully');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'cache_service_stats' => $this->cacheService->getStats(),
            'cache_tags' => $this->cacheTags,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
}
