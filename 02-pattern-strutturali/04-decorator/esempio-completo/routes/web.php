<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/notifications');
});

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/send', [NotificationController::class, 'sendNotification'])->name('notifications.send');
Route::post('/notifications/info', [NotificationController::class, 'getDecoratorInfo'])->name('notifications.info');
Route::post('/notifications/reset-throttling', [NotificationController::class, 'resetThrottling'])->name('notifications.reset-throttling');
