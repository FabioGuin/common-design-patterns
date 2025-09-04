<?php

use App\Http\Controllers\PoolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route per gestione pool
Route::prefix('pools')->group(function () {
    Route::get('/', [PoolController::class, 'index']);
    Route::get('/{poolName}/stats', [PoolController::class, 'getStats']);
    Route::post('/{poolName}/acquire', [PoolController::class, 'acquire']);
    Route::post('/{poolName}/release', [PoolController::class, 'release']);
    Route::get('/{poolName}/health', [PoolController::class, 'healthCheck']);
    Route::post('/{poolName}/cleanup', [PoolController::class, 'cleanup']);
    Route::post('/{poolName}/reset', [PoolController::class, 'reset']);
    Route::post('/{poolName}/stress-test', [PoolController::class, 'stressTest']);
});

// Route per test specifici
Route::prefix('test')->group(function () {
    Route::post('/database-pool', [PoolController::class, 'testDatabasePool']);
    Route::post('/file-pool', [PoolController::class, 'testFilePool']);
    Route::post('/cache-pool', [PoolController::class, 'testCachePool']);
});

// Route per health check globale
Route::get('/health', [PoolController::class, 'healthCheck']);
