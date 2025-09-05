<?php

namespace App\Http\Controllers;

use App\Services\EagerLoadingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EagerLoadingController extends Controller
{
    private EagerLoadingService $eagerLoadingService;

    public function __construct(EagerLoadingService $eagerLoadingService)
    {
        $this->eagerLoadingService = $eagerLoadingService;
    }

    /**
     * Get users with eager loading
     */
    public function getUsersWithEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $users = $this->eagerLoadingService->getUsersWithEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users with eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users without eager loading (N+1 problem)
     */
    public function getUsersWithoutEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $users = $this->eagerLoadingService->getUsersWithoutEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => false,
                'n_plus_1_problem' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users without eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products with eager loading
     */
    public function getProductsWithEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $products = $this->eagerLoadingService->getProductsWithEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products with eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders with eager loading
     */
    public function getOrdersWithEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $orders = $this->eagerLoadingService->getOrdersWithEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $orders,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders with eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with products
     */
    public function getCategoriesWithProducts(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $categories = $this->eagerLoadingService->getCategoriesWithProducts();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $categories,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories with products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $data = $this->eagerLoadingService->getDashboardData();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get selective eager loading
     */
    public function getSelectiveEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $users = $this->eagerLoadingService->getSelectiveEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true,
                'selective' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load selective eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conditional eager loading
     */
    public function getConditionalEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $users = $this->eagerLoadingService->getConditionalEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $users,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true,
                'conditional' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load conditional eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch eager loading
     */
    public function getBatchEagerLoading(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            $result = $this->eagerLoadingService->getBatchEagerLoading();
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'response_time_ms' => round($responseTime, 2),
                'eager_loaded' => true,
                'batch' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load batch eager loading',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get eager loading statistics
     */
    public function getEagerLoadingStats(): JsonResponse
    {
        try {
            $stats = $this->eagerLoadingService->getLoadingStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get eager loading statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset eager loading statistics
     */
    public function resetEagerLoadingStats(): JsonResponse
    {
        try {
            $this->eagerLoadingService->resetStats();

            return response()->json([
                'success' => true,
                'message' => 'Eager loading statistics reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset eager loading statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare eager loading vs N+1
     */
    public function compareEagerLoadingVsNPlusOne(): JsonResponse
    {
        try {
            $startTime = microtime(true);
            
            // Test with eager loading
            $eagerStart = microtime(true);
            $eagerUsers = $this->eagerLoadingService->getUsersWithEagerLoading();
            $eagerTime = (microtime(true) - $eagerStart) * 1000;
            
            // Test without eager loading (N+1)
            $nPlusOneStart = microtime(true);
            $nPlusOneUsers = $this->eagerLoadingService->getUsersWithoutEagerLoading();
            $nPlusOneTime = (microtime(true) - $nPlusOneStart) * 1000;
            
            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'eager_loading' => [
                        'time_ms' => round($eagerTime, 2),
                        'users_count' => count($eagerUsers),
                        'efficient' => true
                    ],
                    'n_plus_1' => [
                        'time_ms' => round($nPlusOneTime, 2),
                        'users_count' => count($nPlusOneUsers),
                        'efficient' => false
                    ],
                    'performance_improvement' => [
                        'time_saved_ms' => round($nPlusOneTime - $eagerTime, 2),
                        'percentage_improvement' => round((($nPlusOneTime - $eagerTime) / $nPlusOneTime) * 100, 2)
                    ],
                    'total_comparison_time_ms' => round($totalTime, 2)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compare eager loading vs N+1',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
