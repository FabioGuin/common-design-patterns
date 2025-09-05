<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OutboxController;

Route::get('/outbox', [OutboxController::class, 'index'])->name('outbox.index');
Route::get('/outbox/orders', [OutboxController::class, 'getOrders'])->name('outbox.orders');
Route::post('/outbox/orders', [OutboxController::class, 'createOrder'])->name('outbox.orders.create');
Route::put('/outbox/orders/{id}', [OutboxController::class, 'updateOrder'])->name('outbox.orders.update');
Route::delete('/outbox/orders/{id}', [OutboxController::class, 'deleteOrder'])->name('outbox.orders.delete');
Route::get('/outbox/orders/{id}/events', [OutboxController::class, 'getOrderEvents'])->name('outbox.orders.events');
Route::get('/outbox/status', [OutboxController::class, 'getStatus'])->name('outbox.status');
Route::post('/outbox/process', [OutboxController::class, 'processEvents'])->name('outbox.process');
Route::post('/outbox/cleanup', [OutboxController::class, 'cleanupEvents'])->name('outbox.cleanup');
Route::get('/outbox/test-connection', [OutboxController::class, 'testConnection'])->name('outbox.test-connection');
Route::get('/outbox/stats', [OutboxController::class, 'getDetailedStats'])->name('outbox.stats');
