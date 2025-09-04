<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIModelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Qui sono definite le route per il sistema di AI Model Abstraction.
| Include dashboard, API endpoints e interfacce web.
|
*/

// Dashboard principale
Route::get('/ai-models/dashboard', [AIModelController::class, 'dashboard'])->name('ai-models.dashboard');

// Confronto modelli
Route::get('/ai-models/comparison', [AIModelController::class, 'modelComparison'])->name('ai-models.comparison');

// Performance e analytics
Route::get('/ai-models/performance', [AIModelController::class, 'performance'])->name('ai-models.performance');

// API Routes
Route::prefix('ai-models/api')->group(function () {
    
    // Generazione contenuto
    Route::post('/generate', [AIModelController::class, 'generateText'])->name('ai-models.api.generate');
    Route::post('/generate-image', [AIModelController::class, 'generateImage'])->name('ai-models.api.generate-image');
    Route::post('/translate', [AIModelController::class, 'translate'])->name('ai-models.api.translate');
    Route::post('/analyze', [AIModelController::class, 'analyzeContent'])->name('ai-models.api.analyze');
    
    // Gestione modelli
    Route::get('/models', [AIModelController::class, 'getModels'])->name('ai-models.api.models');
    Route::get('/models/{model_name}', [AIModelController::class, 'getModelInfo'])->name('ai-models.api.model-info');
    Route::post('/select-model', [AIModelController::class, 'selectBestModel'])->name('ai-models.api.select-model');
    
    // Test e confronto
    Route::post('/test', [AIModelController::class, 'testModel'])->name('ai-models.api.test');
    Route::post('/compare', [AIModelController::class, 'compareModels'])->name('ai-models.api.compare');
    Route::post('/benchmark', [AIModelController::class, 'runBenchmark'])->name('ai-models.api.benchmark');
    
    // Statistiche e performance
    Route::get('/stats', [AIModelController::class, 'getPerformanceStats'])->name('ai-models.api.stats');
    Route::get('/stats/aggregate', [AIModelController::class, 'getAggregateStats'])->name('ai-models.api.stats.aggregate');
    Route::get('/stats/period', [AIModelController::class, 'getPeriodStats'])->name('ai-models.api.stats.period');
    Route::get('/stats/realtime', [AIModelController::class, 'getRealTimeStats'])->name('ai-models.api.stats.realtime');
    
    // Modelli più utilizzati e migliori
    Route::get('/models/most-used', [AIModelController::class, 'getMostUsedModels'])->name('ai-models.api.models.most-used');
    Route::get('/models/best-performing', [AIModelController::class, 'getBestPerformingModels'])->name('ai-models.api.models.best-performing');
    
    // Registry e configurazione
    Route::get('/registry/stats', [AIModelController::class, 'getRegistryStats'])->name('ai-models.api.registry.stats');
    
    // Manutenzione
    Route::post('/cleanup', [AIModelController::class, 'cleanupOldData'])->name('ai-models.api.cleanup');
});

// Route di esempio per test
Route::get('/ai-models/test', function () {
    return view('ai-models.test');
})->name('ai-models.test');

// Route per documentazione API
Route::get('/ai-models/docs', function () {
    return view('ai-models.docs');
})->name('ai-models.docs');
