<?php

use App\Http\Controllers\EagerLoadingController;
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
    return view('eager-loading.demo');
});

// Eager loading routes
Route::prefix('eager')->group(function () {
    Route::get('/users/with', [EagerLoadingController::class, 'getUsersWithEagerLoading']);
    Route::get('/users/without', [EagerLoadingController::class, 'getUsersWithoutEagerLoading']);
    Route::get('/products', [EagerLoadingController::class, 'getProductsWithEagerLoading']);
    Route::get('/orders', [EagerLoadingController::class, 'getOrdersWithEagerLoading']);
    Route::get('/categories', [EagerLoadingController::class, 'getCategoriesWithProducts']);
    Route::get('/dashboard', [EagerLoadingController::class, 'getDashboardData']);
    Route::get('/selective', [EagerLoadingController::class, 'getSelectiveEagerLoading']);
    Route::get('/conditional', [EagerLoadingController::class, 'getConditionalEagerLoading']);
    Route::get('/batch', [EagerLoadingController::class, 'getBatchEagerLoading']);
    Route::get('/stats', [EagerLoadingController::class, 'getEagerLoadingStats']);
    Route::post('/reset-stats', [EagerLoadingController::class, 'resetEagerLoadingStats']);
    Route::get('/compare', [EagerLoadingController::class, 'compareEagerLoadingVsNPlusOne']);
});
