<?php

use App\Http\Controllers\BulkheadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/bulkhead');
});

// Bulkhead Pattern Demo
Route::prefix('bulkhead')->group(function () {
    Route::get('/', [BulkheadController::class, 'index'])->name('bulkhead.index');
    
    // Service Operations
    Route::post('/payment', [BulkheadController::class, 'processPayment'])->name('bulkhead.payment');
    Route::post('/inventory', [BulkheadController::class, 'checkInventory'])->name('bulkhead.inventory');
    Route::post('/notification', [BulkheadController::class, 'sendNotification'])->name('bulkhead.notification');
    Route::post('/report', [BulkheadController::class, 'generateReport'])->name('bulkhead.report');
    
    // Monitoring
    Route::get('/status/{serviceName}', [BulkheadController::class, 'getServiceStatus'])->name('bulkhead.status');
    Route::get('/status', [BulkheadController::class, 'getAllServicesStatus'])->name('bulkhead.all-status');
    Route::get('/metrics', [BulkheadController::class, 'getMetrics'])->name('bulkhead.metrics');
    
    // Management
    Route::post('/reset/{serviceName}', [BulkheadController::class, 'resetBulkhead'])->name('bulkhead.reset');
    Route::post('/reset-all', [BulkheadController::class, 'resetAllBulkheads'])->name('bulkhead.reset-all');
});
