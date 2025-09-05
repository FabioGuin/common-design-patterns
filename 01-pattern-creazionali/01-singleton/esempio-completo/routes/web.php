<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SingletonController;

/*
|--------------------------------------------------------------------------
| Singleton Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Singleton.
|
*/

Route::get('/singleton', [SingletonController::class, 'show']);
Route::get('/singleton/test', [SingletonController::class, 'test']);
Route::get('/singleton/clone-test', [SingletonController::class, 'testClone']);

// Route API
Route::prefix('api/singleton')->group(function () {
    Route::get('/', [SingletonController::class, 'index']);
    Route::post('/test', [SingletonController::class, 'test']);
    Route::get('/clone-test', [SingletonController::class, 'testClone']);
});
