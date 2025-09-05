<?php

use App\Http\Controllers\SagaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/saga');
});

// Saga Pattern Demo
Route::prefix('saga')->group(function () {
    Route::get('/', [SagaController::class, 'index'])->name('saga.index');
    
    // Saga Operations
    Route::post('/execute', [SagaController::class, 'executeOrderSaga'])->name('saga.execute');
    Route::get('/status/{sagaId}', [SagaController::class, 'getSagaStatus'])->name('saga.status');
    Route::get('/all', [SagaController::class, 'getAllSagas'])->name('saga.all');
    Route::get('/stats', [SagaController::class, 'getSagaStats'])->name('saga.stats');
    
    // Service Data
    Route::get('/inventory', [SagaController::class, 'getInventoryReservations'])->name('saga.inventory');
    Route::get('/payments', [SagaController::class, 'getPayments'])->name('saga.payments');
    Route::get('/notifications', [SagaController::class, 'getNotifications'])->name('saga.notifications');
    Route::get('/orders', [SagaController::class, 'getOrders'])->name('saga.orders');
});
