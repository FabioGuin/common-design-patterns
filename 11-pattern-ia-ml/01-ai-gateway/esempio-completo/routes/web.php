<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIGatewayController;

/*
|--------------------------------------------------------------------------
| AI Gateway Routes
|--------------------------------------------------------------------------
|
| Route per il sistema AI Gateway
|
*/

// Dashboard principale
Route::get('/ai-gateway/dashboard', [AIGatewayController::class, 'dashboard'])
    ->name('ai-gateway.dashboard');

// API per generazione contenuti
Route::prefix('ai-gateway/api')->group(function () {
    
    // Generazione testo
    Route::post('/generate-text', [AIGatewayController::class, 'generateText'])
        ->name('ai-gateway.generate-text');
    
    // Generazione immagini
    Route::post('/generate-image', [AIGatewayController::class, 'generateImage'])
        ->name('ai-gateway.generate-image');
    
    // Traduzione
    Route::post('/translate', [AIGatewayController::class, 'translate'])
        ->name('ai-gateway.translate');
    
    // Stato provider
    Route::get('/status', [AIGatewayController::class, 'status'])
        ->name('ai-gateway.status');
    
    // Metriche e statistiche
    Route::get('/metrics', [AIGatewayController::class, 'metrics'])
        ->name('ai-gateway.metrics');
    
    // Test provider
    Route::post('/test-providers', [AIGatewayController::class, 'testProviders'])
        ->name('ai-gateway.test-providers');
    
    // Gestione cache
    Route::post('/cache', [AIGatewayController::class, 'cacheManagement'])
        ->name('ai-gateway.cache');
    
    // Reset rate limits
    Route::post('/reset-rate-limits', [AIGatewayController::class, 'resetRateLimits'])
        ->name('ai-gateway.reset-rate-limits');
    
    // Cronologia richieste
    Route::get('/history', [AIGatewayController::class, 'requestHistory'])
        ->name('ai-gateway.history');
});

// Route di test per sviluppo
if (app()->environment('local')) {
    Route::get('/ai-gateway/test', function () {
        return view('ai-gateway.test');
    })->name('ai-gateway.test');
}
