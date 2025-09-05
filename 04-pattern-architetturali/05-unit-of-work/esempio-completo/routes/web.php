<?php

use App\Http\Controllers\OrderController;
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

// Homepage
Route::get('/', function () {
    return redirect()->route('orders.index');
});

// Route per ordini (Resource Controller)
Route::resource('orders', OrderController::class);

// Route aggiuntive per ordini
Route::prefix('orders')->name('orders.')->group(function () {
    Route::post('{order}/complete', [OrderController::class, 'complete'])->name('complete');
    Route::post('{order}/payment', [OrderController::class, 'processPayment'])->name('payment');
    Route::get('stats', [OrderController::class, 'stats'])->name('stats');
    Route::get('api', [OrderController::class, 'api'])->name('api');
});

// Route per dashboard
Route::get('/dashboard', function () {
    $orderStats = app(OrderService::class)->getOrderStatistics();
    
    return view('dashboard', compact('orderStats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $orderStats = app(OrderService::class)->getOrderStatistics();
    
    return view('stats', compact('orderStats'));
})->name('stats');

// Route per dimostrare il pattern Unit of Work
Route::get('/unit-of-work-demo', function () {
    return view('unit-of-work-demo');
})->name('unit-of-work-demo');
