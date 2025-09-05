<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIRateLimitController;

Route::get('/ai-rate-limit', [AIRateLimitController::class, 'show']);
Route::get('/ai-rate-limit/test', [AIRateLimitController::class, 'test']);
Route::post('/ai-rate-limit/query', [AIRateLimitController::class, 'query']);

Route::prefix('api/ai-rate-limit')->group(function () {
    Route::get('/', [AIRateLimitController::class, 'index']);
    Route::post('/query', [AIRateLimitController::class, 'query']);
    Route::get('/test', [AIRateLimitController::class, 'test']);
});
