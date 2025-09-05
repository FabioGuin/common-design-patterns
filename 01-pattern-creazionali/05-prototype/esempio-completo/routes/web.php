<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/prototype', [DocumentController::class, 'show']);
Route::get('/prototype/test', [DocumentController::class, 'test']);
Route::post('/prototype/clone', [DocumentController::class, 'cloneDocument']);

Route::prefix('api/prototype')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::post('/clone', [DocumentController::class, 'cloneDocument']);
    Route::get('/test', [DocumentController::class, 'test']);
});
