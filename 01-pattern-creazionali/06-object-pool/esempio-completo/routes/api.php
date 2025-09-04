<?php

use App\Http\Controllers\PoolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes per Connection Pool
|--------------------------------------------------------------------------
|
| Route per monitoraggio e gestione dei pool di connessioni
|
*/

Route::prefix('pool')->group(function () {
    
    // Statistiche
    Route::get('/stats', [PoolController::class, 'getStats']);
    Route::get('/stats/{poolName}', [PoolController::class, 'getPoolStats']);
    Route::get('/global-stats', [PoolController::class, 'getGlobalStats']);
    
    // Stato di salute
    Route::get('/health', [PoolController::class, 'getHealth']);
    Route::get('/health/{poolName}', [PoolController::class, 'getPoolHealth']);
    
    // Gestione pool
    Route::get('/list', [PoolController::class, 'getPoolList']);
    Route::post('/create', [PoolController::class, 'createPool']);
    Route::post('/reset/{poolName}', [PoolController::class, 'resetPool']);
    Route::post('/reset-all', [PoolController::class, 'resetAllPools']);
    
});