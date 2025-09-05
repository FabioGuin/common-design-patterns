<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;

/*
|--------------------------------------------------------------------------
| Write-Behind Pattern Routes
|--------------------------------------------------------------------------
|
| Route per testare il pattern Write-Behind.
|
*/

Route::get('/write-behind', [LogController::class, 'index']);
Route::get('/write-behind/test', [LogController::class, 'test']);

// Route API
Route::prefix('api/write-behind')->group(function () {
    Route::get('/', [LogController::class, 'index']);
    Route::post('/test', [LogController::class, 'test']);
    Route::post('/logs', [LogController::class, 'store']);
    Route::get('/logs', [LogController::class, 'list']);
    Route::get('/logs/{id}', [LogController::class, 'show']);
    Route::get('/performance', [LogController::class, 'performanceTest']);
    Route::get('/stress', [LogController::class, 'stressTest']);
    Route::get('/stats', [LogController::class, 'stats']);
    Route::post('/clear-cache', [LogController::class, 'clearCache']);
});
