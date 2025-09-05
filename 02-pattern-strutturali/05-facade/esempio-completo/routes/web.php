<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/orders');
});

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/process', [OrderController::class, 'processOrder'])->name('orders.process');
Route::post('/orders/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
Route::post('/orders/info', [OrderController::class, 'getOrderInfo'])->name('orders.info');
Route::get('/orders/report', [OrderController::class, 'generateReport'])->name('orders.report');
Route::get('/orders/stats', [OrderController::class, 'getSystemStats'])->name('orders.stats');
