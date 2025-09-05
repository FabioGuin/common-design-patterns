<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Hexagonal Architecture Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Hexagonal Architecture.
|
*/

Route::get('/hexagonal-architecture', [OrderController::class, 'index']);
Route::get('/hexagonal-architecture/test', [OrderController::class, 'test']);

// Route API
Route::prefix('api/hexagonal-architecture')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/test', [OrderController::class, 'test']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrder']);
    Route::get('/orders', [OrderController::class, 'listOrders']);
    Route::get('/stats', [OrderController::class, 'getStats']);
    Route::post('/process-payment/{id}', [OrderController::class, 'processPayment']);
    Route::post('/send-notification/{id}', [OrderController::class, 'sendNotification']);
});
