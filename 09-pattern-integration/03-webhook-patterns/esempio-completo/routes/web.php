<?php

use App\Http\Controllers\WebhookController;
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
    return view('webhook-patterns.demo');
});

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::post('/handle', [WebhookController::class, 'handleWebhook']);
    Route::post('/payment', [WebhookController::class, 'handlePaymentWebhook']);
    Route::get('/stats', [WebhookController::class, 'getWebhookStats']);
    Route::get('/logs', [WebhookController::class, 'getWebhookLogs']);
    Route::post('/retry', [WebhookController::class, 'retryWebhook']);
    Route::post('/test', [WebhookController::class, 'testWebhook']);
});
