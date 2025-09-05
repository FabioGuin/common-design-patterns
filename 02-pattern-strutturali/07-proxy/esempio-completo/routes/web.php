<?php

use App\Http\Controllers\DataController;
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
    return redirect('/data');
});

// Rotte per il Proxy Pattern
Route::prefix('data')->group(function () {
    Route::get('/', [DataController::class, 'index'])->name('data.index');
    Route::get('/caching', [DataController::class, 'cachingExample'])->name('data.caching');
    Route::get('/access-control', [DataController::class, 'accessControlExample'])->name('data.access-control');
    Route::get('/logging', [DataController::class, 'loggingExample'])->name('data.logging');
    Route::get('/combined', [DataController::class, 'combinedExample'])->name('data.combined');
    Route::post('/invalidate-cache', [DataController::class, 'invalidateCache'])->name('data.invalidate-cache');
});
