<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileBFFController;

Route::prefix('mobile')->group(function () {
    Route::get('/orders', [MobileBFFController::class, 'getOrders']);
    Route::get('/orders/{id}', [MobileBFFController::class, 'getOrder']);
    Route::get('/dashboard', [MobileBFFController::class, 'getDashboard']);
    Route::get('/products', [MobileBFFController::class, 'getProducts']);
    Route::get('/offline', [MobileBFFController::class, 'getOfflineData']);
    Route::post('/cache/clear', [MobileBFFController::class, 'clearCache']);
});
