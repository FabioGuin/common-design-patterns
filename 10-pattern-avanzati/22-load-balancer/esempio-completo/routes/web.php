<?php

use App\Http\Controllers\LoadBalancerController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

// Route per il pattern Load Balancer
Route::get('/load-balancer', [LoadBalancerController::class, 'index']);
Route::get('/load-balancer/test', [LoadBalancerController::class, 'test']);

// Route API
Route::prefix('api/load-balancer')->group(function () {
    Route::get('/', [LoadBalancerController::class, 'index']);
    Route::post('/test', [LoadBalancerController::class, 'test']);
    Route::get('/servers', [LoadBalancerController::class, 'servers']);
    Route::get('/health', [LoadBalancerController::class, 'health']);
    Route::post('/add-server', [LoadBalancerController::class, 'addServer']);
    Route::delete('/remove-server/{id}', [LoadBalancerController::class, 'removeServer']);
    Route::post('/set-algorithm', [LoadBalancerController::class, 'setAlgorithm']);
    Route::post('/load-test', [LoadBalancerController::class, 'loadTest']);
    Route::get('/stats', [LoadBalancerController::class, 'stats']);
});

// Route per simulare i server
Route::prefix('server')->group(function () {
    Route::get('/{id}', [ServerController::class, 'handle']);
    Route::get('/{id}/health', [ServerController::class, 'health']);
    Route::get('/{id}/stats', [ServerController::class, 'stats']);
    Route::post('/{id}/simulate-down', [ServerController::class, 'simulateDown']);
    Route::post('/{id}/restore', [ServerController::class, 'restore']);
    Route::post('/{id}/simulate-error', [ServerController::class, 'simulateError']);
});
