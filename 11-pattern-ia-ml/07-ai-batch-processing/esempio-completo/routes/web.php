<?php

use App\Http\Controllers\AIBatchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Batch Processing Routes
|--------------------------------------------------------------------------
|
| Queste route dimostrano il pattern AI Batch Processing
| per l'elaborazione efficiente di grandi quantità di richieste AI.
|
*/

// Pagina principale del batch processing
Route::get('/', [AIBatchController::class, 'index'])->name('ai-batch.index');

// API per la gestione dei batch
Route::prefix('api/batch')->group(function () {
    // Creazione e gestione batch
    Route::post('/create', [AIBatchController::class, 'createBatch'])->name('ai-batch.create');
    Route::post('/sample', [AIBatchController::class, 'createSampleBatch'])->name('ai-batch.sample');
    Route::post('/{batchId}/process', [AIBatchController::class, 'processBatch'])->name('ai-batch.process');
    Route::post('/{batchId}/cancel', [AIBatchController::class, 'cancelBatch'])->name('ai-batch.cancel');
    Route::post('/{batchId}/retry', [AIBatchController::class, 'retryBatch'])->name('ai-batch.retry');
    
    // Consultazione batch
    Route::get('/{batchId}/status', [AIBatchController::class, 'getBatchStatus'])->name('ai-batch.status');
    Route::get('/', [AIBatchController::class, 'getBatches'])->name('ai-batch.list');
    Route::get('/statistics', [AIBatchController::class, 'getStatistics'])->name('ai-batch.statistics');
});
