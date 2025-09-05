<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;

Route::get('/builder', [EmailController::class, 'show']);
Route::get('/builder/test', [EmailController::class, 'test']);
Route::post('/builder/create', [EmailController::class, 'createEmail']);

Route::prefix('api/builder')->group(function () {
    Route::get('/', [EmailController::class, 'index']);
    Route::post('/create', [EmailController::class, 'createEmail']);
    Route::get('/test', [EmailController::class, 'test']);
});
