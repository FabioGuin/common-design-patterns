<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MicroservicesController;

/*
|--------------------------------------------------------------------------
| Microservices Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Microservices.
|
*/

Route::get('/microservices', [MicroservicesController::class, 'index']);
Route::get('/microservices/test', [MicroservicesController::class, 'test']);

// Route API
Route::prefix('api/microservices')->group(function () {
    Route::get('/', [MicroservicesController::class, 'index']);
    Route::post('/test', [MicroservicesController::class, 'test']);
    
    // User Service
    Route::post('/users', [MicroservicesController::class, 'createUser']);
    Route::get('/users/{id}', [MicroservicesController::class, 'getUser']);
    
    // Product Service
    Route::post('/products', [MicroservicesController::class, 'createProduct']);
    Route::get('/products/{id}', [MicroservicesController::class, 'getProduct']);
    Route::get('/products', [MicroservicesController::class, 'listProducts']);
    
    // Order Service
    Route::post('/orders', [MicroservicesController::class, 'createOrder']);
    Route::get('/orders/{id}', [MicroservicesController::class, 'getOrder']);
    Route::get('/orders', [MicroservicesController::class, 'listOrders']);
    
    // Payment Service
    Route::post('/payments', [MicroservicesController::class, 'processPayment']);
    Route::get('/payments/{id}', [MicroservicesController::class, 'getPayment']);
    
    // Service Discovery
    Route::get('/services', [MicroservicesController::class, 'listServices']);
    Route::get('/health', [MicroservicesController::class, 'healthCheck']);
});
