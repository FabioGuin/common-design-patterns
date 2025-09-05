<?php

use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\ProductServiceController;
use App\Http\Controllers\OrderServiceController;
use App\Http\Controllers\PaymentServiceController;
use Illuminate\Support\Facades\Route;

// Route per il pattern Database Per Service
Route::get('/database-per-service', [ApiGatewayController::class, 'index']);
Route::get('/database-per-service/test', [ApiGatewayController::class, 'test']);

// Route API Gateway
Route::prefix('api/database-per-service')->group(function () {
    Route::get('/', [ApiGatewayController::class, 'index']);
    Route::post('/test', [ApiGatewayController::class, 'test']);
    Route::get('/services', [ApiGatewayController::class, 'services']);
    Route::get('/stats', [ApiGatewayController::class, 'stats']);
    
    // User Service
    Route::get('/users', [UserServiceController::class, 'index']);
    Route::post('/users', [UserServiceController::class, 'store']);
    Route::get('/users/{id}', [UserServiceController::class, 'show']);
    Route::put('/users/{id}', [UserServiceController::class, 'update']);
    Route::delete('/users/{id}', [UserServiceController::class, 'destroy']);
    Route::get('/users/{id}/stats', [UserServiceController::class, 'stats']);
    
    // Product Service
    Route::get('/products', [ProductServiceController::class, 'index']);
    Route::post('/products', [ProductServiceController::class, 'store']);
    Route::get('/products/{id}', [ProductServiceController::class, 'show']);
    Route::put('/products/{id}', [ProductServiceController::class, 'update']);
    Route::delete('/products/{id}', [ProductServiceController::class, 'destroy']);
    Route::post('/products/{id}/inventory', [ProductServiceController::class, 'updateInventory']);
    Route::get('/products/{id}/stats', [ProductServiceController::class, 'stats']);
    
    // Order Service
    Route::get('/orders', [OrderServiceController::class, 'index']);
    Route::post('/orders', [OrderServiceController::class, 'store']);
    Route::get('/orders/{id}', [OrderServiceController::class, 'show']);
    Route::put('/orders/{id}/status', [OrderServiceController::class, 'updateStatus']);
    Route::delete('/orders/{id}', [OrderServiceController::class, 'destroy']);
    Route::get('/orders/{id}/stats', [OrderServiceController::class, 'stats']);
    
    // Payment Service
    Route::get('/payments', [PaymentServiceController::class, 'index']);
    Route::post('/payments', [PaymentServiceController::class, 'store']);
    Route::get('/payments/{id}', [PaymentServiceController::class, 'show']);
    Route::post('/payments/{id}/process', [PaymentServiceController::class, 'process']);
    Route::post('/payments/{id}/refund', [PaymentServiceController::class, 'refund']);
    Route::get('/payments/{id}/stats', [PaymentServiceController::class, 'stats']);
});
