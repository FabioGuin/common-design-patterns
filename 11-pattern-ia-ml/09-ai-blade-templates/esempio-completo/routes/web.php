<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIBladeController;

// Route per il pattern AI Blade Templates
Route::get('/ai-blade', [AIBladeController::class, 'show']);
Route::get('/ai-blade/test', [AIBladeController::class, 'test']);

// Route API
Route::prefix('api/ai-blade')->group(function () {
    Route::get('/', [AIBladeController::class, 'show']);
    Route::post('/render', [AIBladeController::class, 'render']);
    Route::post('/translate', [AIBladeController::class, 'translate']);
    Route::post('/personalize', [AIBladeController::class, 'personalize']);
    Route::post('/test', [AIBladeController::class, 'test']);
});
