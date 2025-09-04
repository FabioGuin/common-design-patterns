<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PromptController;

/*
|--------------------------------------------------------------------------
| Prompt Engineering Routes
|--------------------------------------------------------------------------
|
| Route per il sistema di Prompt Engineering
|
*/

// Dashboard principale
Route::get('/prompt/dashboard', [PromptController::class, 'dashboard'])
    ->name('prompt.dashboard');

// Editor template
Route::get('/prompt/editor/{templateName?}', [PromptController::class, 'templateEditor'])
    ->name('prompt.editor');

// API per generazione e test
Route::prefix('prompt/api')->group(function () {
    
    // Genera contenuto
    Route::post('/generate', [PromptController::class, 'generate'])
        ->name('prompt.generate');
    
    // Testa template
    Route::post('/test', [PromptController::class, 'testTemplate'])
        ->name('prompt.test');
    
    // A/B testing
    Route::post('/ab-test', [PromptController::class, 'runABTest'])
        ->name('prompt.ab-test');
    
    // Ottimizza template
    Route::post('/optimize', [PromptController::class, 'optimizeTemplate'])
        ->name('prompt.optimize');
    
    // Lista template
    Route::get('/templates', [PromptController::class, 'getTemplates'])
        ->name('prompt.templates');
    
    // Dettagli template
    Route::get('/template/{templateName}', [PromptController::class, 'getTemplateDetails'])
        ->name('prompt.template.details');
    
    // Valida output
    Route::post('/validate', [PromptController::class, 'validateOutput'])
        ->name('prompt.validate');
    
    // Analytics
    Route::get('/analytics', [PromptController::class, 'getAnalytics'])
        ->name('prompt.analytics');
    
    // Cronologia test
    Route::get('/history', [PromptController::class, 'getTestHistory'])
        ->name('prompt.history');
    
    // Gestione template personalizzati
    Route::post('/template/save', [PromptController::class, 'saveTemplate'])
        ->name('prompt.template.save');
    
    Route::delete('/template/{templateName}', [PromptController::class, 'deleteTemplate'])
        ->name('prompt.template.delete');
});

// Route di test per sviluppo
if (app()->environment('local')) {
    Route::get('/prompt/test', function () {
        return view('prompt.test');
    })->name('prompt.test');
}
