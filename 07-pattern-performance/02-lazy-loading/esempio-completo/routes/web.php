<?php

use App\Http\Controllers\LazyLoadingController;
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
    return view('lazy-loading.demo');
});

// Lazy loading routes
Route::prefix('lazy')->group(function () {
    Route::get('/user/{id}', [LazyLoadingController::class, 'getLazyUser']);
    Route::get('/product/{id}', [LazyLoadingController::class, 'getLazyProduct']);
    Route::get('/order/{id}', [LazyLoadingController::class, 'getLazyOrder']);
    Route::get('/stats', [LazyLoadingController::class, 'getLazyLoadingStats']);
    Route::post('/clear-cache', [LazyLoadingController::class, 'clearLazyLoadingCache']);
    Route::post('/test-performance', [LazyLoadingController::class, 'testLazyLoadingPerformance']);
});
