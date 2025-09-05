<?php

namespace App\Http\Controllers;

use App\Services\ProductCacheService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    private ProductCacheService $productCacheService;
    private CacheService $cacheService;

    public function __construct(ProductCacheService $productCacheService, CacheService $cacheService)
    {
        $this->productCacheService = $productCacheService;
        $this->cacheService = $cacheService;
    }

    /**
     * Get all products
     */
    public function index(): JsonResponse
    {
        $products = $this->productCacheService->getAllProducts();
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'cached' => true
        ]);
    }

    /**
     * Get product by ID
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productCacheService->getProductById($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $product,
            'cached' => true
        ]);
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        $products = $this->productCacheService->getProductsByCategory($categoryId);
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'cached' => true
        ]);
    }

    /**
     * Get featured products
     */
    public function getFeatured(): JsonResponse
    {
        $products = $this->productCacheService->getFeaturedProducts();
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'cached' => true
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }
        
        $products = $this->productCacheService->searchProducts($query);
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'query' => $query,
            'cached' => true
        ]);
    }

    /**
     * Get product statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->productCacheService->getProductStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'cached' => true
        ]);
    }

    /**
     * Clear product cache
     */
    public function clearCache(int $id = null): JsonResponse
    {
        $this->productCacheService->invalidateProductCache($id);
        
        return response()->json([
            'success' => true,
            'message' => $id ? "Cache cleared for product {$id}" : 'All product cache cleared'
        ]);
    }

    /**
     * Warm up cache
     */
    public function warmUpCache(): JsonResponse
    {
        $this->productCacheService->warmUpCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Cache warmed up successfully'
        ]);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): JsonResponse
    {
        $stats = $this->productCacheService->getCacheStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
