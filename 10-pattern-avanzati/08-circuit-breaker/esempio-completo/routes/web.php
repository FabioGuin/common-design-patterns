<?php

use App\Http\Controllers\CircuitBreakerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/circuit-breaker');
});

// Circuit Breaker Pattern Demo
Route::prefix('circuit-breaker')->group(function () {
    Route::get('/', [CircuitBreakerController::class, 'index'])->name('circuit-breaker.index');
    
    // Service Operations
    Route::post('/payment', [CircuitBreakerController::class, 'processPayment'])->name('circuit-breaker.payment');
    Route::post('/inventory', [CircuitBreakerController::class, 'checkInventory'])->name('circuit-breaker.inventory');
    Route::post('/notification', [CircuitBreakerController::class, 'sendNotification'])->name('circuit-breaker.notification');
    
    // Monitoring
    Route::get('/status/{serviceName}', [CircuitBreakerController::class, 'getServiceStatus'])->name('circuit-breaker.status');
    Route::get('/status', [CircuitBreakerController::class, 'getAllServicesStatus'])->name('circuit-breaker.all-status');
    Route::get('/metrics', [CircuitBreakerController::class, 'getMetrics'])->name('circuit-breaker.metrics');
    
    // Management
    Route::post('/reset/{serviceName}', [CircuitBreakerController::class, 'resetCircuitBreaker'])->name('circuit-breaker.reset');
    Route::post('/reset-all', [CircuitBreakerController::class, 'resetAllCircuitBreakers'])->name('circuit-breaker.reset-all');
});
