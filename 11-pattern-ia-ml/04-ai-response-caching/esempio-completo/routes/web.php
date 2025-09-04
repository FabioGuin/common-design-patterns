<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AICacheController;

/*
|--------------------------------------------------------------------------
| AI Cache Routes
|--------------------------------------------------------------------------
|
| Route per il sistema di AI Response Caching
|
*/

// Dashboard e pagine web
Route::prefix('ai-cache')->name('ai-cache.')->group(function () {
    Route::get('/dashboard', [AICacheController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [AICacheController::class, 'analytics'])->name('analytics');
    Route::get('/management', [AICacheController::class, 'management'])->name('management');
});

// API Routes per operazioni cache
Route::prefix('ai-cache/api')->name('ai-cache.api.')->group(function () {
    // Operazioni base
    Route::post('/cache', [AICacheController::class, 'put'])->name('put');
    Route::get('/cache/{key}', [AICacheController::class, 'get'])->name('get');
    Route::get('/cache/{key}/exists', [AICacheController::class, 'has'])->name('has');
    Route::delete('/cache/{key}', [AICacheController::class, 'forget'])->name('forget');
    
    // Invalidazione
    Route::post('/invalidate/pattern', [AICacheController::class, 'invalidateByPattern'])->name('invalidate.pattern');
    Route::post('/invalidate/tag', [AICacheController::class, 'invalidateByTag'])->name('invalidate.tag');
    
    // Cache warming
    Route::post('/warm', [AICacheController::class, 'warm'])->name('warm');
    
    // Analytics e metriche
    Route::get('/analytics', [AICacheController::class, 'getAnalytics'])->name('analytics');
    Route::get('/performance', [AICacheController::class, 'getPerformanceMetrics'])->name('performance');
    Route::get('/cost-savings', [AICacheController::class, 'getCostSavings'])->name('cost-savings');
    Route::get('/stats', [AICacheController::class, 'getCacheStats'])->name('stats');
    Route::get('/recommendations', [AICacheController::class, 'getRecommendations'])->name('recommendations');
    Route::get('/health', [AICacheController::class, 'getHealthMetrics'])->name('health');
    
    // Gestione cache
    Route::get('/cache/{key}/info', [AICacheController::class, 'getKeyInfo'])->name('key.info');
    Route::post('/optimize', [AICacheController::class, 'optimize'])->name('optimize');
    Route::post('/flush', [AICacheController::class, 'flush'])->name('flush');
});

// Route di test per dimostrare il funzionamento
Route::prefix('ai-cache/test')->name('ai-cache.test.')->group(function () {
    Route::get('/basic', function () {
        $cacheService = app(\App\Services\AI\AICacheService::class);
        
        // Test di base
        $testKey = 'test_basic_' . time();
        $testData = [
            'message' => 'Hello from AI Cache!',
            'timestamp' => now()->toISOString(),
            'test' => true
        ];
        
        // Salva in cache
        $putSuccess = $cacheService->put($testKey, $testData, [
            'strategy' => 'lru',
            'ttl' => 3600,
            'tags' => ['test', 'basic']
        ]);
        
        // Recupera dalla cache
        $cachedData = $cacheService->get($testKey);
        
        // Verifica esistenza
        $exists = $cacheService->has($testKey);
        
        return response()->json([
            'test' => 'Basic Cache Operations',
            'put_success' => $putSuccess,
            'cached_data' => $cachedData,
            'exists' => $exists,
            'data_matches' => $cachedData === $testData
        ]);
    })->name('basic');
    
    Route::get('/performance', function () {
        $cacheService = app(\App\Services\AI\AICacheService::class);
        
        // Test di performance
        $iterations = 100;
        $times = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $key = 'perf_test_' . $i;
            $data = ['iteration' => $i, 'data' => str_repeat('x', 1000)];
            
            $start = microtime(true);
            $cacheService->put($key, $data, ['strategy' => 'lru', 'ttl' => 60]);
            $times[] = microtime(true) - $start;
        }
        
        $avgTime = array_sum($times) / count($times);
        $minTime = min($times);
        $maxTime = max($times);
        
        return response()->json([
            'test' => 'Performance Test',
            'iterations' => $iterations,
            'avg_time' => round($avgTime * 1000, 3) . 'ms',
            'min_time' => round($minTime * 1000, 3) . 'ms',
            'max_time' => round($maxTime * 1000, 3) . 'ms',
            'total_time' => round(array_sum($times) * 1000, 3) . 'ms'
        ]);
    })->name('performance');
    
    Route::get('/strategies', function () {
        $cacheService = app(\App\Services\AI\AICacheService::class);
        
        // Test delle diverse strategie
        $strategies = ['lru', 'lfu', 'ttl', 'fifo'];
        $results = [];
        
        foreach ($strategies as $strategy) {
            $key = 'strategy_test_' . $strategy . '_' . time();
            $data = ['strategy' => $strategy, 'timestamp' => now()->toISOString()];
            
            $start = microtime(true);
            $putSuccess = $cacheService->put($key, $data, [
                'strategy' => $strategy,
                'ttl' => 60
            ]);
            $putTime = microtime(true) - $start;
            
            $start = microtime(true);
            $cachedData = $cacheService->get($key);
            $getTime = microtime(true) - $start;
            
            $results[$strategy] = [
                'put_success' => $putSuccess,
                'put_time' => round($putTime * 1000, 3) . 'ms',
                'get_time' => round($getTime * 1000, 3) . 'ms',
                'data_retrieved' => $cachedData !== null
            ];
        }
        
        return response()->json([
            'test' => 'Strategy Comparison',
            'strategies' => $results
        ]);
    })->name('strategies');
    
    Route::get('/analytics', function () {
        $analyticsService = app(\App\Services\AI\CacheAnalyticsService::class);
        
        // Genera alcuni dati di test
        $cacheService = app(\App\Services\AI\AICacheService::class);
        
        for ($i = 0; $i < 10; $i++) {
            $key = 'analytics_test_' . $i;
            $data = ['test' => true, 'iteration' => $i];
            
            $cacheService->put($key, $data, ['strategy' => 'lru', 'ttl' => 60]);
            $cacheService->get($key); // Simula hit
        }
        
        // Ottieni analytics
        $analytics = $analyticsService->getAnalytics();
        $performance = $analyticsService->getPerformanceMetrics();
        $costSavings = $analyticsService->getCostSavings();
        $health = $analyticsService->getHealthMetrics();
        
        return response()->json([
            'test' => 'Analytics Test',
            'analytics' => $analytics,
            'performance' => $performance,
            'cost_savings' => $costSavings,
            'health' => $health
        ]);
    })->name('analytics');
    
    Route::get('/cleanup', function () {
        $cacheService = app(\App\Services\AI\AICacheService::class);
        
        // Pulisci i dati di test
        $testKeys = [
            'test_basic_',
            'perf_test_',
            'strategy_test_',
            'analytics_test_'
        ];
        
        $cleaned = 0;
        foreach ($testKeys as $pattern) {
            $count = $cacheService->invalidateByPattern($pattern . '*');
            $cleaned += $count;
        }
        
        return response()->json([
            'test' => 'Cleanup Test',
            'keys_cleaned' => $cleaned,
            'message' => 'Test data cleaned successfully'
        ]);
    })->name('cleanup');
});
