<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/payments');
});

Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

Route::post('/payments/process', [PaymentController::class, 'processPayment'])->name('payments.process');
Route::post('/payments/status', [PaymentController::class, 'getPaymentStatus'])->name('payments.status');
Route::post('/payments/refund', [PaymentController::class, 'refundPayment'])->name('payments.refund');
Route::post('/payments/switch-provider', [PaymentController::class, 'switchProvider'])->name('payments.switch-provider');

// Route per PayPal (callback)
Route::get('/payments/success', function () {
    return view('payments.success');
})->name('payments.success');

Route::get('/payments/cancel', function () {
    return view('payments.cancel');
})->name('payments.cancel');
