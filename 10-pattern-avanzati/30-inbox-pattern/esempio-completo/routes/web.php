<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;

Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
Route::post('/inbox/events', [InboxController::class, 'receiveEvent'])->name('inbox.events.receive');
Route::post('/inbox/simulate', [InboxController::class, 'simulateEvent'])->name('inbox.simulate');
Route::get('/inbox/status', [InboxController::class, 'getStatus'])->name('inbox.status');
Route::get('/inbox/events/{eventType}', [InboxController::class, 'getEventsByType'])->name('inbox.events.type');
Route::post('/inbox/process', [InboxController::class, 'processEvents'])->name('inbox.process');
Route::post('/inbox/cleanup', [InboxController::class, 'cleanupEvents'])->name('inbox.cleanup');
Route::get('/inbox/test-connection', [InboxController::class, 'testConnection'])->name('inbox.test-connection');
Route::get('/inbox/stats', [InboxController::class, 'getDetailedStats'])->name('inbox.stats');
Route::get('/inbox/duplicates', [InboxController::class, 'getDuplicateEvents'])->name('inbox.duplicates');
Route::post('/inbox/restore-stuck', [InboxController::class, 'restoreStuckEvents'])->name('inbox.restore-stuck');
Route::get('/inbox/orders', [InboxController::class, 'getOrders'])->name('inbox.orders');
