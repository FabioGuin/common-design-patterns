<?php

use App\Http\Controllers\AuthorizationController;
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
    return view('authorization.demo');
});

// Authorization routes
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::post('/check-permission', [AuthorizationController::class, 'checkPermission']);
    Route::post('/check-role', [AuthorizationController::class, 'checkRole']);
    Route::get('/permissions', [AuthorizationController::class, 'getUserPermissions']);
    Route::get('/roles', [AuthorizationController::class, 'getUserRoles']);
    Route::post('/assign-role', [AuthorizationController::class, 'assignRole']);
    Route::post('/remove-role', [AuthorizationController::class, 'removeRole']);
    Route::get('/stats', [AuthorizationController::class, 'getAuthorizationStats']);
});
