<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NullObjectController;

/*
|--------------------------------------------------------------------------
| Null Object Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Null Object.
|
*/

Route::get('/null-object', [NullObjectController::class, 'index']);
Route::get('/null-object/test', [NullObjectController::class, 'test']);

// Route API
Route::prefix('api/null-object')->group(function () {
    Route::get('/', [NullObjectController::class, 'index']);
    Route::post('/test', [NullObjectController::class, 'test']);
    Route::get('/test-all', [NullObjectController::class, 'testAll']);
    Route::get('/info', [NullObjectController::class, 'info']);
});
