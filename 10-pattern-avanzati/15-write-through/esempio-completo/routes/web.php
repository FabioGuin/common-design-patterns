<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Write-Through Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Write-Through.
|
*/

Route::get('/write-through', [ProductController::class, 'index']);
Route::get('/write-through/test', [ProductController::class, 'test']);

// Route API
Route::prefix('api/write-through')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/test', [ProductController::class, 'test']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products', [ProductController::class, 'list']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/performance', [ProductController::class, 'performanceTest']);
});
