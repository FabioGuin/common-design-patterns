<?php

use App\Http\Controllers\ValidationController;
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
    return view('input-validation.demo');
});

// Validation routes
Route::prefix('validation')->group(function () {
    Route::post('/user', [ValidationController::class, 'validateUser']);
    Route::post('/product', [ValidationController::class, 'validateProduct']);
    Route::post('/order', [ValidationController::class, 'validateOrder']);
    Route::post('/sanitize', [ValidationController::class, 'sanitizeInput']);
    Route::post('/custom-rules', [ValidationController::class, 'validateWithCustomRules']);
    Route::get('/stats', [ValidationController::class, 'getValidationStats']);
    Route::post('/test-rules', [ValidationController::class, 'testValidationRules']);
});
