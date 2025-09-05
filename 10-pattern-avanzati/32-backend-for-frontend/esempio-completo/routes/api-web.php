<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebBFFController;

Route::prefix('web')->group(function () {
    Route::get('/orders', [WebBFFController::class, 'getOrders']);
    Route::get('/orders/{id}', [WebBFFController::class, 'getOrder']);
    Route::get('/dashboard', [WebBFFController::class, 'getDashboard']);
    Route::get('/products', [WebBFFController::class, 'getProducts']);
    Route::post('/cache/clear', [WebBFFController::class, 'clearCache']);
});
