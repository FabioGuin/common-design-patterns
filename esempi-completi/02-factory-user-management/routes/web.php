<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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
    return view('welcome');
});

// Demo routes per testare le factory
Route::get('/demo/factory', function () {
    return view('factory-demo');
});

Route::post('/demo/create-user', [UserController::class, 'createUser']);
Route::post('/demo/create-admin', [UserController::class, 'createAdmin']);
Route::post('/demo/create-guest', [UserController::class, 'createGuest']);
