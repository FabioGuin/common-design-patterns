<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainEventController;

/*
|--------------------------------------------------------------------------
| Domain Event Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Domain Event.
|
*/

Route::get('/domain-event', [DomainEventController::class, 'index']);
Route::get('/domain-event/test', [DomainEventController::class, 'test']);

// Route API
Route::prefix('api/domain-event')->group(function () {
    Route::get('/', [DomainEventController::class, 'index']);
    Route::post('/test', [DomainEventController::class, 'test']);
    Route::post('/order/confirm', [DomainEventController::class, 'confirmOrder']);
    Route::post('/order/cancel', [DomainEventController::class, 'cancelOrder']);
    Route::post('/order/ship', [DomainEventController::class, 'shipOrder']);
    Route::post('/payment/process', [DomainEventController::class, 'processPayment']);
    Route::post('/payment/fail', [DomainEventController::class, 'failPayment']);
});
