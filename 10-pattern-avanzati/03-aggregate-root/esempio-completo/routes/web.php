<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AggregateRootController;

/*
|--------------------------------------------------------------------------
| Aggregate Root Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Aggregate Root.
|
*/

Route::get('/aggregate-root', [AggregateRootController::class, 'index']);
Route::get('/aggregate-root/test', [AggregateRootController::class, 'test']);

// Route API
Route::prefix('api/aggregate-root')->group(function () {
    Route::get('/', [AggregateRootController::class, 'index']);
    Route::post('/test', [AggregateRootController::class, 'test']);
    Route::post('/order/create', [AggregateRootController::class, 'createOrder']);
    Route::post('/order/{id}/add-item', [AggregateRootController::class, 'addItem']);
    Route::post('/order/{id}/confirm', [AggregateRootController::class, 'confirmOrder']);
    Route::post('/order/{id}/cancel', [AggregateRootController::class, 'cancelOrder']);
});
