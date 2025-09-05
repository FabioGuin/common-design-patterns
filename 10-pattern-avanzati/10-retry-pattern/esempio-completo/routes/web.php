<?php

use App\Http\Controllers\RetryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/retry');
});

// Retry Pattern Demo
Route::prefix('retry')->group(function () {
    Route::get('/', [RetryController::class, 'index'])->name('retry.index');
    
    // Service Operations
    Route::post('/payment', [RetryController::class, 'processPayment'])->name('retry.payment');
    Route::post('/inventory', [RetryController::class, 'checkInventory'])->name('retry.inventory');
    Route::post('/notification', [RetryController::class, 'sendNotification'])->name('retry.notification');
    
    // Monitoring
    Route::get('/status/{serviceName}', [RetryController::class, 'getServiceStatus'])->name('retry.status');
    Route::get('/status', [RetryController::class, 'getAllServicesStatus'])->name('retry.all-status');
    Route::get('/metrics', [RetryController::class, 'getMetrics'])->name('retry.metrics');
    Route::get('/attempts', [RetryController::class, 'getRetryAttempts'])->name('retry.attempts');
});
