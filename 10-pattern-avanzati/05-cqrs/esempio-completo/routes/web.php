<?php

use App\Http\Controllers\CQRSController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/cqrs');
});

// CQRS Pattern Demo
Route::prefix('cqrs')->group(function () {
    Route::get('/', [CQRSController::class, 'index'])->name('cqrs.index');
    
    // COMMAND SIDE - Scrittura
    Route::post('/products', [CQRSController::class, 'createProduct'])->name('cqrs.products.create');
    Route::put('/products/{id}', [CQRSController::class, 'updateProduct'])->name('cqrs.products.update');
    Route::post('/orders', [CQRSController::class, 'createOrder'])->name('cqrs.orders.create');
    
    // QUERY SIDE - Lettura
    Route::get('/products/search', [CQRSController::class, 'searchProducts'])->name('cqrs.products.search');
    Route::get('/products/{id}', [CQRSController::class, 'getProduct'])->name('cqrs.products.show');
    Route::get('/products/stats', [CQRSController::class, 'getProductStats'])->name('cqrs.products.stats');
    Route::get('/orders/user/{userId}', [CQRSController::class, 'getOrdersByUser'])->name('cqrs.orders.user');
    Route::get('/orders/stats', [CQRSController::class, 'getOrderStats'])->name('cqrs.orders.stats');
});
