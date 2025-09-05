<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PromptController;

Route::get('/prompt-engineering', [PromptController::class, 'show']);
Route::get('/prompt-engineering/test', [PromptController::class, 'test']);
Route::post('/prompt-engineering/generate', [PromptController::class, 'generatePrompt']);

Route::prefix('api/prompt-engineering')->group(function () {
    Route::get('/', [PromptController::class, 'index']);
    Route::post('/generate', [PromptController::class, 'generatePrompt']);
    Route::get('/test', [PromptController::class, 'test']);
});
