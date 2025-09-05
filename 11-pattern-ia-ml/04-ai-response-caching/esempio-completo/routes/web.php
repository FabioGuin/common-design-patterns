<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AICacheController;

Route::get('/ai-response-caching', [AICacheController::class, 'show']);
Route::get('/ai-response-caching/test', [AICacheController::class, 'test']);
Route::post('/ai-response-caching/query', [AICacheController::class, 'query']);

Route::prefix('api/ai-response-caching')->group(function () {
    Route::get('/', [AICacheController::class, 'index']);
    Route::post('/query', [AICacheController::class, 'query']);
    Route::get('/test', [AICacheController::class, 'test']);
});
