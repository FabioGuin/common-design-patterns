<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UIComponentController;

/*
|--------------------------------------------------------------------------
| Abstract Factory Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Abstract Factory.
|
*/

Route::get('/abstract-factory', [UIComponentController::class, 'show']);
Route::get('/abstract-factory/test', [UIComponentController::class, 'test']);
Route::post('/abstract-factory/create', [UIComponentController::class, 'createComponents']);

// Route API
Route::prefix('api/abstract-factory')->group(function () {
    Route::get('/', [UIComponentController::class, 'index']);
    Route::post('/create', [UIComponentController::class, 'createComponents']);
    Route::get('/test', [UIComponentController::class, 'test']);
});
