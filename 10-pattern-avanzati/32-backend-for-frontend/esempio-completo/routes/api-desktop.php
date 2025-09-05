<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesktopBFFController;

Route::prefix('desktop')->group(function () {
    Route::get('/orders', [DesktopBFFController::class, 'getOrders']);
    Route::get('/orders/{id}', [DesktopBFFController::class, 'getOrder']);
    Route::get('/dashboard', [DesktopBFFController::class, 'getDashboard']);
    Route::get('/products', [DesktopBFFController::class, 'getProducts']);
    Route::get('/export', [DesktopBFFController::class, 'getExportData']);
    Route::post('/cache/clear', [DesktopBFFController::class, 'clearCache']);
});
