<?php

use App\Http\Controllers\CachingAsideController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/caching-aside');
});

// Caching Aside Pattern Demo
Route::prefix('caching-aside')->group(function () {
    Route::get('/', [CachingAsideController::class, 'index'])->name('caching-aside.index');
    
    // Products
    Route::get('/products', [CachingAsideController::class, 'getAllProducts'])->name('caching-aside.products.all');
    Route::get('/products/category/{category}', [CachingAsideController::class, 'getProductsByCategory'])->name('caching-aside.products.category');
    Route::get('/products/{id}', [CachingAsideController::class, 'getProduct'])->name('caching-aside.products.get');
    Route::post('/products', [CachingAsideController::class, 'createProduct'])->name('caching-aside.products.create');
    Route::put('/products/{id}', [CachingAsideController::class, 'updateProduct'])->name('caching-aside.products.update');
    Route::delete('/products/{id}', [CachingAsideController::class, 'deleteProduct'])->name('caching-aside.products.delete');
    Route::post('/products/{id}/refresh', [CachingAsideController::class, 'refreshProduct'])->name('caching-aside.products.refresh');
    Route::post('/products/preload', [CachingAsideController::class, 'preloadProducts'])->name('caching-aside.products.preload');
    
    // Users
    Route::get('/users', [CachingAsideController::class, 'getAllUsers'])->name('caching-aside.users.all');
    Route::get('/users/status/{status}', [CachingAsideController::class, 'getUsersByStatus'])->name('caching-aside.users.status');
    Route::get('/users/{id}', [CachingAsideController::class, 'getUser'])->name('caching-aside.users.get');
    Route::post('/users', [CachingAsideController::class, 'createUser'])->name('caching-aside.users.create');
    Route::put('/users/{id}', [CachingAsideController::class, 'updateUser'])->name('caching-aside.users.update');
    Route::delete('/users/{id}', [CachingAsideController::class, 'deleteUser'])->name('caching-aside.users.delete');
    Route::post('/users/{id}/refresh', [CachingAsideController::class, 'refreshUser'])->name('caching-aside.users.refresh');
    Route::post('/users/preload', [CachingAsideController::class, 'preloadUsers'])->name('caching-aside.users.preload');
    
    // Orders
    Route::get('/orders', [CachingAsideController::class, 'getAllOrders'])->name('caching-aside.orders.all');
    Route::get('/orders/user/{userId}', [CachingAsideController::class, 'getOrdersByUser'])->name('caching-aside.orders.user');
    Route::get('/orders/status/{status}', [CachingAsideController::class, 'getOrdersByStatus'])->name('caching-aside.orders.status');
    Route::get('/orders/{id}', [CachingAsideController::class, 'getOrder'])->name('caching-aside.orders.get');
    Route::post('/orders', [CachingAsideController::class, 'createOrder'])->name('caching-aside.orders.create');
    Route::put('/orders/{id}', [CachingAsideController::class, 'updateOrder'])->name('caching-aside.orders.update');
    Route::delete('/orders/{id}', [CachingAsideController::class, 'deleteOrder'])->name('caching-aside.orders.delete');
    Route::post('/orders/{id}/refresh', [CachingAsideController::class, 'refreshOrder'])->name('caching-aside.orders.refresh');
    Route::post('/orders/preload', [CachingAsideController::class, 'preloadOrders'])->name('caching-aside.orders.preload');
    
    // Cache Stats
    Route::get('/stats/{entity}', [CachingAsideController::class, 'getCacheStats'])->name('caching-aside.stats');
    Route::get('/stats', [CachingAsideController::class, 'getAllCacheStats'])->name('caching-aside.stats.all');
});
