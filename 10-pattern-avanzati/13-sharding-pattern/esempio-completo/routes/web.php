<?php

use App\Http\Controllers\ShardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/sharding');
});

// Sharding Pattern Demo
Route::prefix('sharding')->group(function () {
    Route::get('/', [ShardingController::class, 'index'])->name('sharding.index');
    
    // Create Operations
    Route::post('/users', [ShardingController::class, 'createUser'])->name('sharding.create-user');
    Route::post('/products', [ShardingController::class, 'createProduct'])->name('sharding.create-product');
    Route::post('/orders', [ShardingController::class, 'createOrder'])->name('sharding.create-order');
    
    // Read Operations
    Route::get('/users/{id}', [ShardingController::class, 'getUser'])->name('sharding.get-user');
    Route::get('/products/{id}', [ShardingController::class, 'getProduct'])->name('sharding.get-product');
    Route::get('/orders/{id}', [ShardingController::class, 'getOrder'])->name('sharding.get-order');
    
    // List Operations
    Route::get('/users', [ShardingController::class, 'getAllUsers'])->name('sharding.all-users');
    Route::get('/products', [ShardingController::class, 'getAllProducts'])->name('sharding.all-products');
    Route::get('/orders', [ShardingController::class, 'getAllOrders'])->name('sharding.all-orders');
    
    // Monitoring
    Route::get('/status/{entity}', [ShardingController::class, 'getShardingStatus'])->name('sharding.status');
    Route::get('/status', [ShardingController::class, 'getAllShardingStatus'])->name('sharding.all-status');
    Route::get('/metrics', [ShardingController::class, 'getMetrics'])->name('sharding.metrics');
});
