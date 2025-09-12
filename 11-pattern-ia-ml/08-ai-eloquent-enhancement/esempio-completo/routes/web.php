<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIEloquentController;

// Route per il pattern AI Eloquent Enhancement
Route::get('/ai-eloquent', [AIEloquentController::class, 'show']);
Route::get('/ai-eloquent/test', [AIEloquentController::class, 'test']);

// Route API
Route::prefix('api/ai-eloquent')->group(function () {
    Route::get('/', [AIEloquentController::class, 'show']);
    Route::post('/search', [AIEloquentController::class, 'search']);
    Route::post('/generate-tags', [AIEloquentController::class, 'generateTags']);
    Route::post('/translate', [AIEloquentController::class, 'translate']);
    Route::post('/related', [AIEloquentController::class, 'related']);
    Route::post('/test', [AIEloquentController::class, 'test']);
});
