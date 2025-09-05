<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiGatewayController;

/*
|--------------------------------------------------------------------------
| API Gateway Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern API Gateway.
|
*/

Route::get('/api-gateway', [ApiGatewayController::class, 'index']);
Route::get('/api-gateway/test', [ApiGatewayController::class, 'test']);

// Route API Gateway
Route::prefix('api/v1')->middleware(['api.gateway'])->group(function () {
    Route::get('/', [ApiGatewayController::class, 'index']);
    Route::post('/test', [ApiGatewayController::class, 'test']);
    
    // User Service
    Route::prefix('users')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listUsers']);
        Route::post('/', [ApiGatewayController::class, 'createUser']);
        Route::get('/{id}', [ApiGatewayController::class, 'getUser']);
        Route::put('/{id}', [ApiGatewayController::class, 'updateUser']);
        Route::delete('/{id}', [ApiGatewayController::class, 'deleteUser']);
    });
    
    // Product Service
    Route::prefix('products')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listProducts']);
        Route::post('/', [ApiGatewayController::class, 'createProduct']);
        Route::get('/{id}', [ApiGatewayController::class, 'getProduct']);
        Route::put('/{id}', [ApiGatewayController::class, 'updateProduct']);
    });
    
    // Order Service
    Route::prefix('orders')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listOrders']);
        Route::post('/', [ApiGatewayController::class, 'createOrder']);
        Route::get('/{id}', [ApiGatewayController::class, 'getOrder']);
        Route::put('/{id}/status', [ApiGatewayController::class, 'updateOrderStatus']);
    });
    
    // Payment Service
    Route::prefix('payments')->group(function () {
        Route::get('/', [ApiGatewayController::class, 'listPayments']);
        Route::post('/', [ApiGatewayController::class, 'processPayment']);
        Route::get('/{id}', [ApiGatewayController::class, 'getPayment']);
        Route::post('/{id}/refund', [ApiGatewayController::class, 'refundPayment']);
    });
    
    // Gateway Management
    Route::prefix('gateway')->group(function () {
        Route::get('/health', [ApiGatewayController::class, 'healthCheck']);
        Route::get('/stats', [ApiGatewayController::class, 'getStats']);
        Route::get('/services', [ApiGatewayController::class, 'listServices']);
    });
});
