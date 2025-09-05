<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIFallbackController;

Route::get('/ai-fallback', [AIFallbackController::class, 'show']);
Route::get('/ai-fallback/test', [AIFallbackController::class, 'test']);
Route::post('/ai-fallback/query', [AIFallbackController::class, 'query']);

Route::prefix('api/ai-fallback')->group(function () {
    Route::get('/', [AIFallbackController::class, 'index']);
    Route::post('/query', [AIFallbackController::class, 'query']);
    Route::get('/test', [AIFallbackController::class, 'test']);
});
