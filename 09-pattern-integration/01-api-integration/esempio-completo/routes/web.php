<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('api-integration.demo');
});

// Payment routes
Route::prefix('payments')->group(function () {
    Route::post('/', [PaymentController::class, 'processPayment']);
    Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::get('/{id}', [PaymentController::class, 'getPaymentStatus']);
    Route::post('/{id}/refund', [PaymentController::class, 'refundPayment']);
});

// Customer routes
Route::prefix('customers')->group(function () {
    Route::post('/', [PaymentController::class, 'createCustomer']);
    Route::get('/{id}', [PaymentController::class, 'getCustomer']);
});

// API management routes
Route::prefix('api')->group(function () {
    Route::get('/stats', [PaymentController::class, 'getApiStats']);
    Route::post('/cache/clear', [PaymentController::class, 'clearCache']);
});
