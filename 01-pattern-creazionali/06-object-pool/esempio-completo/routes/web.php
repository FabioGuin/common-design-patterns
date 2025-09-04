<?php

use App\Http\Controllers\PoolController;
use App\Services\DatabaseService;
use App\Services\PoolManager;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes per Connection Pool
|--------------------------------------------------------------------------
|
| Route per test e dimostrazione del Connection Pool System
|
*/

Route::get('/', function () {
    return view('pool-dashboard');
});

// Route per test del pool
Route::get('/test-pool', function () {
    try {
        $poolManager = PoolManager::getInstance();
        $pool = $poolManager->getPool('default');
        
        // Test di base
        $connection = $pool->acquire();
        $stmt = $connection->prepare('SELECT 1 as test');
        $stmt->execute();
        $result = $stmt->fetch();
        $pool->release($connection);
        
        return response()->json([
            'success' => true,
            'message' => 'Pool test completato con successo',
            'test_result' => $result,
            'pool_stats' => $pool->getStats()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Route per test del DatabaseService
Route::get('/test-database-service', function () {
    try {
        $service = new DatabaseService('default');
        
        // Test di una query semplice
        $users = $service->getUsersByRole('user', 5);
        
        return response()->json([
            'success' => true,
            'message' => 'DatabaseService test completato',
            'users_found' => count($users),
            'pool_stats' => $service->getPoolStats()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Route per test di performance
Route::get('/test-performance', function () {
    try {
        $poolManager = PoolManager::getInstance();
        $pool = $poolManager->getPool('default');
        
        $startTime = microtime(true);
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $connection = $pool->acquire();
            $stmt = $connection->prepare('SELECT ? as iteration');
            $stmt->execute([$i]);
            $result = $stmt->fetch();
            $pool->release($connection);
        }
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        return response()->json([
            'success' => true,
            'message' => 'Performance test completato',
            'iterations' => $iterations,
            'duration_seconds' => round($duration, 4),
            'operations_per_second' => round($iterations / $duration, 2),
            'pool_stats' => $pool->getStats()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
