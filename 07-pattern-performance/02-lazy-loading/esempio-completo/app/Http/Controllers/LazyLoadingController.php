<?php

namespace App\Http\Controllers;

use App\Services\LazyLoadingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LazyLoadingController extends Controller
{
    private LazyLoadingService $lazyLoadingService;

    public function __construct(LazyLoadingService $lazyLoadingService)
    {
        $this->lazyLoadingService = $lazyLoadingService;
    }

    /**
     * Get lazy user
     */
    public function getLazyUser(int $userId): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $lazyUser = $this->lazyLoadingService->getLazyUser($userId);
            
            // Access properties to trigger lazy loading
            $userData = [
                'id' => $lazyUser->id,
                'name' => $lazyUser->name,
                'email' => $lazyUser->email,
                'profile' => $lazyUser->profile,
                'statistics' => $lazyUser->statistics
            ];
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $userData,
                'response_time_ms' => round($responseTime, 2),
                'lazy_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lazy product
     */
    public function getLazyProduct(int $productId): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $lazyProduct = $this->lazyLoadingService->getLazyProduct($productId);
            
            // Access properties to trigger lazy loading
            $productData = [
                'id' => $lazyProduct->id,
                'name' => $lazyProduct->name,
                'description' => $lazyProduct->description,
                'price' => $lazyProduct->price,
                'category' => $lazyProduct->category,
                'images' => $lazyProduct->images,
                'reviews' => $lazyProduct->reviews,
                'inventory' => $lazyProduct->inventory
            ];
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $productData,
                'response_time_ms' => round($responseTime, 2),
                'lazy_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lazy order
     */
    public function getLazyOrder(int $orderId): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $lazyOrder = $this->lazyLoadingService->getLazyOrder($orderId);
            
            // Access properties to trigger lazy loading
            $orderData = [
                'id' => $lazyOrder->id,
                'user_id' => $lazyOrder->user_id,
                'status' => $lazyOrder->status,
                'total' => $lazyOrder->total,
                'items' => $lazyOrder->items,
                'shipping' => $lazyOrder->shipping,
                'payment' => $lazyOrder->payment
            ];
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $orderData,
                'response_time_ms' => round($responseTime, 2),
                'lazy_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lazy loading statistics
     */
    public function getLazyLoadingStats(): JsonResponse
    {
        try {
            $stats = $this->lazyLoadingService->getLoadingStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get lazy loading statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear lazy loading cache
     */
    public function clearLazyLoadingCache(): JsonResponse
    {
        try {
            $this->lazyLoadingService->clearLoadedObjects();

            return response()->json([
                'success' => true,
                'message' => 'Lazy loading cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear lazy loading cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test lazy loading performance
     */
    public function testLazyLoadingPerformance(Request $request): JsonResponse
    {
        try {
            $iterations = $request->input('iterations', 10);
            $results = [];

            for ($i = 0; $i < $iterations; $i++) {
                $startTime = microtime(true);
                
                // Test user lazy loading
                $lazyUser = $this->lazyLoadingService->getLazyUser(rand(1, 100));
                $userData = $lazyUser->toArray();
                
                $endTime = microtime(true);
                $responseTime = ($endTime - $startTime) * 1000;
                
                $results[] = [
                    'iteration' => $i + 1,
                    'response_time_ms' => round($responseTime, 2),
                    'objects_loaded' => count($userData)
                ];
            }

            $averageTime = array_sum(array_column($results, 'response_time_ms')) / count($results);

            return response()->json([
                'success' => true,
                'data' => [
                    'iterations' => $iterations,
                    'results' => $results,
                    'average_response_time_ms' => round($averageTime, 2),
                    'total_objects_loaded' => array_sum(array_column($results, 'objects_loaded'))
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to test lazy loading performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
