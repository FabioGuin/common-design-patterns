<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIGatewayController;

Route::get('/ai-gateway', [AIGatewayController::class, 'show']);
Route::get('/ai-gateway/test', [AIGatewayController::class, 'test']);
Route::post('/ai-gateway/chat', [AIGatewayController::class, 'chat']);

Route::prefix('api/ai-gateway')->group(function () {
    Route::get('/', [AIGatewayController::class, 'index']);
    Route::post('/chat', [AIGatewayController::class, 'chat']);
    Route::get('/test', [AIGatewayController::class, 'test']);
});
