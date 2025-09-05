<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventDrivenController;

Route::get('/event-driven', [EventDrivenController::class, 'index'])->name('event-driven.index');
Route::post('/event-driven/orders', [EventDrivenController::class, 'createOrder'])->name('event-driven.orders.create');
Route::put('/event-driven/orders/{id}', [EventDrivenController::class, 'updateOrder'])->name('event-driven.orders.update');
Route::post('/event-driven/orders/{id}/payment', [EventDrivenController::class, 'processPayment'])->name('event-driven.orders.payment');
Route::get('/event-driven/orders', [EventDrivenController::class, 'getOrders'])->name('event-driven.orders');
Route::get('/event-driven/orders/{id}/events', [EventDrivenController::class, 'getOrderEvents'])->name('event-driven.orders.events');
Route::get('/event-driven/events', [EventDrivenController::class, 'getEvents'])->name('event-driven.events');
Route::get('/event-driven/stats', [EventDrivenController::class, 'getStats'])->name('event-driven.stats');
Route::post('/event-driven/replay', [EventDrivenController::class, 'replayEvents'])->name('event-driven.replay');
Route::get('/event-driven/test-event-bus', [EventDrivenController::class, 'testEventBus'])->name('event-driven.test-event-bus');
Route::post('/event-driven/cleanup', [EventDrivenController::class, 'cleanupEvents'])->name('event-driven.cleanup');
Route::get('/event-driven/subscriptions', [EventDrivenController::class, 'getSubscriptions'])->name('event-driven.subscriptions');
