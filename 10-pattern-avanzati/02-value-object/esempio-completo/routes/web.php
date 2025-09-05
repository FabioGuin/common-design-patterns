<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValueObjectController;

/*
|--------------------------------------------------------------------------
| Value Object Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Value Object.
|
*/

Route::get('/value-object', [ValueObjectController::class, 'index']);
Route::get('/value-object/test', [ValueObjectController::class, 'test']);

// Route API
Route::prefix('api/value-object')->group(function () {
    Route::get('/', [ValueObjectController::class, 'index']);
    Route::post('/test', [ValueObjectController::class, 'test']);
    Route::post('/price/calculate', [ValueObjectController::class, 'calculatePrice']);
    Route::post('/address/validate', [ValueObjectController::class, 'validateAddress']);
});
