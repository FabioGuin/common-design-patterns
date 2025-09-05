<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('caching-strategies.demo');
});

// Product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/featured', [ProductController::class, 'getFeatured']);
    Route::get('/stats', [ProductController::class, 'getStats']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/category/{id}', [ProductController::class, 'getByCategory']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

// Cache management routes
Route::prefix('cache')->group(function () {
    Route::post('/clear', [ProductController::class, 'clearCache']);
    Route::post('/clear/{id}', [ProductController::class, 'clearCache']);
    Route::post('/warm-up', [ProductController::class, 'warmUpCache']);
    Route::get('/stats', [ProductController::class, 'getCacheStats']);
});

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index']);
