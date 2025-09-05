<?php

use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/email');
});

Route::get('/email', [EmailController::class, 'index'])->name('email.demo');
Route::post('/email/register', [EmailController::class, 'registerUser'])->name('email.register');
Route::post('/email/newsletter', [EmailController::class, 'sendNewsletter'])->name('email.newsletter');
Route::post('/email/notification', [EmailController::class, 'sendNotification'])->name('email.notification');
Route::get('/email/status', [EmailController::class, 'getQueueStatus'])->name('email.status');
