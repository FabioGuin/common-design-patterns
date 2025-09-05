<?php

use App\Http\Controllers\StranglerFigController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\ModernController;
use Illuminate\Support\Facades\Route;

// Route per il pattern Strangler Fig
Route::get('/strangler-fig', [StranglerFigController::class, 'index']);
Route::get('/strangler-fig/test', [StranglerFigController::class, 'test']);

// Route API
Route::prefix('api/strangler-fig')->group(function () {
    Route::get('/', [StranglerFigController::class, 'index']);
    Route::post('/test', [StranglerFigController::class, 'test']);
    Route::get('/status', [StranglerFigController::class, 'status']);
    Route::post('/migrate-feature', [StranglerFigController::class, 'migrateFeature']);
    Route::post('/rollback-feature', [StranglerFigController::class, 'rollbackFeature']);
    Route::post('/update-percentage', [StranglerFigController::class, 'updateMigrationPercentage']);
    Route::post('/complete-migration', [StranglerFigController::class, 'completeMigration']);
    Route::get('/features', [StranglerFigController::class, 'features']);
    Route::post('/test-request', [StranglerFigController::class, 'testRequest']);
    Route::get('/stats', [StranglerFigController::class, 'stats']);
});

// Route legacy (simulate)
Route::prefix('legacy')->group(function () {
    Route::get('/users', [LegacyController::class, 'users']);
    Route::get('/products', [LegacyController::class, 'products']);
    Route::get('/orders', [LegacyController::class, 'orders']);
    Route::get('/error', [LegacyController::class, 'simulateError']);
    Route::get('/stats', [LegacyController::class, 'stats']);
});

// Route modern (new system)
Route::prefix('modern')->group(function () {
    Route::get('/users', [ModernController::class, 'users']);
    Route::get('/products', [ModernController::class, 'products']);
    Route::get('/orders', [ModernController::class, 'orders']);
    Route::get('/error', [ModernController::class, 'simulateError']);
    Route::get('/stats', [ModernController::class, 'stats']);
});
