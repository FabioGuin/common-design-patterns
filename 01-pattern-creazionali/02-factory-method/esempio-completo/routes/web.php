<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Factory Method Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Factory Method.
|
*/

Route::get('/factory-method', [UserController::class, 'show']);
Route::get('/factory-method/test', [UserController::class, 'test']);
Route::post('/factory-method/create', [UserController::class, 'createUser']);

// Route API
Route::prefix('api/factory-method')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/create', [UserController::class, 'createUser']);
    Route::get('/test', [UserController::class, 'test']);
});
