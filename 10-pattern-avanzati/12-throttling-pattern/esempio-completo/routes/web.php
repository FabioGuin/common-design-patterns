<?php

use App\Http\Controllers\ThrottlingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/throttling');
});

// Throttling Pattern Demo
Route::prefix('throttling')->group(function () {
    Route::get('/', [ThrottlingController::class, 'index'])->name('throttling.index');
    
    // Service Operations
    Route::post('/payment', [ThrottlingController::class, 'processPayment'])->name('throttling.payment');
    Route::post('/inventory', [ThrottlingController::class, 'checkInventory'])->name('throttling.inventory');
    Route::post('/notification', [ThrottlingController::class, 'sendNotification'])->name('throttling.notification');
    
    // Monitoring
    Route::get('/status', [ThrottlingController::class, 'getServiceStatus'])->name('throttling.status');
    Route::get('/status/all', [ThrottlingController::class, 'getAllServicesStatus'])->name('throttling.all-status');
    Route::get('/metrics', [ThrottlingController::class, 'getMetrics'])->name('throttling.metrics');
    Route::get('/events', [ThrottlingController::class, 'getThrottlingEvents'])->name('throttling.events');
});
