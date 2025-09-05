<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;

/*
|--------------------------------------------------------------------------
| Materialized View Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Materialized View.
|
*/

Route::get('/materialized-view', [SalesController::class, 'index']);
Route::get('/materialized-view/test', [SalesController::class, 'test']);

// Route API
Route::prefix('api/materialized-view')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::post('/test', [SalesController::class, 'test']);
    Route::get('/reports/sales-by-category', [SalesController::class, 'salesByCategory']);
    Route::get('/reports/sales-by-month', [SalesController::class, 'salesByMonth']);
    Route::get('/reports/top-products', [SalesController::class, 'topProducts']);
    Route::get('/reports/daily-sales', [SalesController::class, 'dailySales']);
    Route::post('/refresh', [SalesController::class, 'refreshViews']);
    Route::get('/status', [SalesController::class, 'viewStatus']);
    Route::get('/performance-comparison', [SalesController::class, 'performanceComparison']);
    Route::post('/create-test-data', [SalesController::class, 'createTestData']);
});
