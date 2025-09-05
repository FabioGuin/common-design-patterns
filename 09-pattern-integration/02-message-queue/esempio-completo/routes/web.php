<?php

use App\Http\Controllers\QueueController;
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
    return view('message-queue.demo');
});

// Queue routes
Route::prefix('queue')->group(function () {
    Route::post('/email', [QueueController::class, 'sendEmail']);
    Route::post('/order', [QueueController::class, 'processOrder']);
    Route::get('/stats', [QueueController::class, 'getQueueStats']);
    Route::get('/failed', [QueueController::class, 'getFailedJobs']);
    Route::post('/retry', [QueueController::class, 'retryFailedJob']);
    Route::post('/clear-failed', [QueueController::class, 'clearFailedJobs']);
    Route::get('/status', [QueueController::class, 'getQueueStatus']);
});
