<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\AI\AICacheService;
use App\Services\AI\CacheAnalyticsService;
use App\Services\AI\CacheWarmingService;
use App\Services\AI\CacheInvalidationService;
use App\Models\AICacheEntry;
use App\Models\CacheHit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AICacheController extends Controller
{
    private AICacheService $cacheService;
    private CacheAnalyticsService $analyticsService;
    private CacheWarmingService $warmingService;
    private CacheInvalidationService $invalidationService;

    public function __construct(
        AICacheService $cacheService,
        CacheAnalyticsService $analyticsService,
        CacheWarmingService $warmingService,
        CacheInvalidationService $invalidationService
    ) {
        $this->cacheService = $cacheService;
        $this->analyticsService = $analyticsService;
        $this->warmingService = $warmingService;
        $this->invalidationService = $invalidationService;
    }

    /**
     * Dashboard principale della cache AI
     */
    public function dashboard(): View
    {
        $stats = $this->cacheService->getCacheStats();
        $analytics = $this->analyticsService->getAnalytics();
        $performance = $this->analyticsService->getPerformanceMetrics();
        $costSavings = $this->analyticsService->getCostSavings();
        $healthMetrics = $this->analyticsService->getHealthMetrics();
        $recommendations = $this->analyticsService->getOptimizationRecommendations();

        return view('ai-cache.dashboard', compact(
            'stats',
            'analytics',
            'performance',
            'costSavings',
            'healthMetrics',
            'recommendations'
        ));
    }

    /**
     * Pagina analytics dettagliate
     */
    public function analytics(Request $request): View
    {
        $period = $request->get('period', '7d');
        $strategy = $request->get('strategy');

        $analytics = $this->analyticsService->getAnalytics([
            'period' => $period,
            'strategy' => $strategy
        ]);

        $dailyStats = $this->analyticsService->getDailyStats(7);
        $hourlyStats = $this->analyticsService->getHourlyStats(24);
        $strategyStats = $this->analyticsService->getStatsByStrategy();
        $responseTimeStats = $this->analyticsService->getResponseTimeStats();
        $trendStats = $this->analyticsService->getTrendStats();

        return view('ai-cache.analytics', compact(
            'analytics',
            'dailyStats',
            'hourlyStats',
            'strategyStats',
            'responseTimeStats',
            'trendStats',
            'period',
            'strategy'
        ));
    }

    /**
     * Pagina gestione cache
     */
    public function management(Request $request): View
    {
        $strategy = $request->get('strategy');
        $pattern = $request->get('pattern', '*');
        $limit = $request->get('limit', 50);

        $cacheKeys = $this->cacheService->getCacheKeys([
            'strategy' => $strategy,
            'pattern' => $pattern,
            'limit' => $limit
        ]);

        $problematicKeys = $this->analyticsService->getProblematicKeys();
        $tagStats = $this->analyticsService->getStatsByTag();

        return view('ai-cache.management', compact(
            'cacheKeys',
            'problematicKeys',
            'tagStats',
            'strategy',
            'pattern',
            'limit'
        ));
    }

    /**
     * API: Salva risposta in cache
     */
    public function put(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'data' => 'required|array',
            'strategy' => 'string|in:lru,lfu,ttl,fifo,custom',
            'ttl' => 'integer|min:1|max:86400',
            'tags' => 'array',
            'compress' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $success = $this->cacheService->put(
                $request->input('key'),
                $request->input('data'),
                [
                    'strategy' => $request->input('strategy'),
                    'ttl' => $request->input('ttl'),
                    'tags' => $request->input('tags', []),
                    'compress' => $request->input('compress')
                ]
            );

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Data cached successfully' : 'Failed to cache data'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cache data via API', [
                'key' => $request->input('key'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Recupera risposta dalla cache
     */
    public function get(Request $request, string $key): JsonResponse
    {
        $validator = Validator::make(['key' => $key], [
            'key' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $this->cacheService->get($key, [
                'strategy' => $request->input('strategy'),
                'update_stats' => $request->input('update_stats', true)
            ]);

            if ($data !== null) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'cached' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => null,
                'cached' => false
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve data from cache via API', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Verifica se una chiave esiste
     */
    public function has(Request $request, string $key): JsonResponse
    {
        try {
            $exists = $this->cacheService->has($key, [
                'strategy' => $request->input('strategy')
            ]);

            return response()->json([
                'success' => true,
                'exists' => $exists
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check cache key existence via API', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Rimuove una chiave dalla cache
     */
    public function forget(Request $request, string $key): JsonResponse
    {
        try {
            $success = $this->cacheService->forget($key, [
                'strategy' => $request->input('strategy')
            ]);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Key removed successfully' : 'Failed to remove key'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove cache key via API', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Invalida cache per pattern
     */
    public function invalidateByPattern(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pattern' => 'required|string|max:255',
            'strategy' => 'string|in:lru,lfu,ttl,fifo,custom'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $count = $this->cacheService->invalidateByPattern(
                $request->input('pattern'),
                [
                    'strategy' => $request->input('strategy')
                ]
            );

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Invalidated {$count} entries"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by pattern via API', [
                'pattern' => $request->input('pattern'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Invalida cache per tag
     */
    public function invalidateByTag(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tag' => 'required|string|max:255',
            'strategy' => 'string|in:lru,lfu,ttl,fifo,custom'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $count = $this->cacheService->invalidateByTag(
                $request->input('tag'),
                [
                    'strategy' => $request->input('strategy')
                ]
            );

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Invalidated {$count} entries"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by tag via API', [
                'tag' => $request->input('tag'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Pre-riscalda la cache
     */
    public function warm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'array',
            'strategies' => 'array',
            'async' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $data = $request->input('data');
            $strategies = $request->input('strategies', []);
            $async = $request->input('async', true);

            $result = $this->cacheService->warmCache($data);

            return response()->json([
                'success' => true,
                'result' => $result,
                'message' => 'Cache warming completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to warm cache via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene analytics della cache
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        try {
            $analytics = $this->analyticsService->getAnalytics([
                'period' => $request->input('period', '7d'),
                'strategy' => $request->input('strategy')
            ]);

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cache analytics via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene metriche di performance
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $metrics = $this->analyticsService->getPerformanceMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene risparmi di costo
     */
    public function getCostSavings(): JsonResponse
    {
        try {
            $savings = $this->analyticsService->getCostSavings();

            return response()->json([
                'success' => true,
                'savings' => $savings
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cost savings via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene statistiche della cache
     */
    public function getCacheStats(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getCacheStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cache stats via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene informazioni su una chiave
     */
    public function getKeyInfo(Request $request, string $key): JsonResponse
    {
        try {
            $info = $this->cacheService->getKeyInfo($key, [
                'strategy' => $request->input('strategy')
            ]);

            if ($info === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'info' => $info
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get key info via API', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottimizza la cache
     */
    public function optimize(Request $request): JsonResponse
    {
        try {
            $result = $this->cacheService->optimize([
                'strategy' => $request->input('strategy'),
                'aggressive' => $request->input('aggressive', false)
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'message' => 'Cache optimization completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to optimize cache via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Pulisce tutta la cache
     */
    public function flush(Request $request): JsonResponse
    {
        try {
            $success = $this->cacheService->flush([
                'strategy' => $request->input('strategy')
            ]);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Cache flushed successfully' : 'Failed to flush cache'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to flush cache via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene raccomandazioni per l'ottimizzazione
     */
    public function getRecommendations(): JsonResponse
    {
        try {
            $recommendations = $this->analyticsService->getOptimizationRecommendations();

            return response()->json([
                'success' => true,
                'recommendations' => $recommendations
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get optimization recommendations via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API: Ottiene metriche di salute
     */
    public function getHealthMetrics(): JsonResponse
    {
        try {
            $health = $this->analyticsService->getHealthMetrics();

            return response()->json([
                'success' => true,
                'health' => $health
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get health metrics via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
