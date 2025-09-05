<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIModelController;

Route::get('/ai-model-abstraction', [AIModelController::class, 'show']);
Route::get('/ai-model-abstraction/test', [AIModelController::class, 'test']);
Route::post('/ai-model-abstraction/predict', [AIModelController::class, 'predict']);

Route::prefix('api/ai-model-abstraction')->group(function () {
    Route::get('/', [AIModelController::class, 'index']);
    Route::post('/predict', [AIModelController::class, 'predict']);
    Route::get('/test', [AIModelController::class, 'test']);
});
