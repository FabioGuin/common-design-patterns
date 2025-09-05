<?php

use App\Http\Controllers\TimeoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/timeout');
});

// Timeout Pattern Demo
Route::prefix('timeout')->group(function () {
    Route::get('/', [TimeoutController::class, 'index'])->name('timeout.index');
    
    // Service Operations
    Route::post('/payment', [TimeoutController::class, 'processPayment'])->name('timeout.payment');
    Route::post('/inventory', [TimeoutController::class, 'checkInventory'])->name('timeout.inventory');
    Route::post('/notification', [TimeoutController::class, 'sendNotification'])->name('timeout.notification');
    
    // Monitoring
    Route::get('/status/{serviceName}', [TimeoutController::class, 'getServiceStatus'])->name('timeout.status');
    Route::get('/status', [TimeoutController::class, 'getAllServicesStatus'])->name('timeout.all-status');
    Route::get('/metrics', [TimeoutController::class, 'getMetrics'])->name('timeout.metrics');
    Route::get('/events', [TimeoutController::class, 'getTimeoutEvents'])->name('timeout.events');
});
