<?php

use App\Http\Controllers\EventSourcingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/event-sourcing');
});

// Event Sourcing Pattern Demo
Route::prefix('event-sourcing')->group(function () {
    Route::get('/', [EventSourcingController::class, 'index'])->name('event-sourcing.index');
    
    // Order Commands
    Route::post('/orders', [EventSourcingController::class, 'createOrder'])->name('event-sourcing.orders.create');
    Route::post('/orders/{orderId}/pay', [EventSourcingController::class, 'payOrder'])->name('event-sourcing.orders.pay');
    Route::post('/orders/{orderId}/ship', [EventSourcingController::class, 'shipOrder'])->name('event-sourcing.orders.ship');
    Route::post('/orders/{orderId}/deliver', [EventSourcingController::class, 'deliverOrder'])->name('event-sourcing.orders.deliver');
    Route::post('/orders/{orderId}/cancel', [EventSourcingController::class, 'cancelOrder'])->name('event-sourcing.orders.cancel');
    Route::post('/orders/{orderId}/refund', [EventSourcingController::class, 'refundOrder'])->name('event-sourcing.orders.refund');
    
    // Order Queries
    Route::get('/orders', [EventSourcingController::class, 'getAllOrders'])->name('event-sourcing.orders.index');
    Route::get('/orders/{orderId}', [EventSourcingController::class, 'getOrder'])->name('event-sourcing.orders.show');
    Route::get('/orders/{orderId}/events', [EventSourcingController::class, 'getOrderEvents'])->name('event-sourcing.orders.events');
    Route::get('/events', [EventSourcingController::class, 'getAllEvents'])->name('event-sourcing.events.index');
});
