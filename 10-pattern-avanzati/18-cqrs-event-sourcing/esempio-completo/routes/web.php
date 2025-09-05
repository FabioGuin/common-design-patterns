<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| CQRS + Event Sourcing Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern CQRS + Event Sourcing.
|
*/

Route::get('/cqrs-event-sourcing', [OrderController::class, 'index']);
Route::get('/cqrs-event-sourcing/test', [OrderController::class, 'test']);

// Route API
Route::prefix('api/cqrs-event-sourcing')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/test', [OrderController::class, 'test']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrder']);
    Route::get('/orders', [OrderController::class, 'listOrders']);
    Route::get('/events/{id}', [OrderController::class, 'getOrderEvents']);
    Route::get('/audit/{id}', [OrderController::class, 'getAuditTrail']);
    Route::post('/replay/{id}', [OrderController::class, 'replayEvents']);
    Route::get('/stats', [OrderController::class, 'getOrderStats']);
    Route::get('/event-store-stats', [OrderController::class, 'getEventStoreStats']);
    Route::post('/rebuild', [OrderController::class, 'rebuildProjection']);
});
