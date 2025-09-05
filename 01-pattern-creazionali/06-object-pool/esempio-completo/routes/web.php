<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConnectionController;

Route::get('/object-pool', [ConnectionController::class, 'show']);
Route::get('/object-pool/test', [ConnectionController::class, 'test']);
Route::post('/object-pool/acquire', [ConnectionController::class, 'acquireConnection']);

Route::prefix('api/object-pool')->group(function () {
    Route::get('/', [ConnectionController::class, 'index']);
    Route::post('/acquire', [ConnectionController::class, 'acquireConnection']);
    Route::get('/test', [ConnectionController::class, 'test']);
});
