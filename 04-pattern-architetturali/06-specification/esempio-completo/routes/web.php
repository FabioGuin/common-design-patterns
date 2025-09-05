<?php

use App\Http\Controllers\ProductController;
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
    return redirect()->route('products.index');
});

// Route per prodotti (Resource Controller)
Route::resource('products', ProductController::class);

// Route aggiuntive per prodotti
Route::prefix('products')->name('products.')->group(function () {
    Route::get('api', [ProductController::class, 'api'])->name('api');
    Route::get('available', [ProductController::class, 'available'])->name('available');
    Route::get('price-range', [ProductController::class, 'priceRange'])->name('price-range');
    Route::get('category/{categoryId}', [ProductController::class, 'byCategory'])->name('by-category');
    Route::get('search', [ProductController::class, 'search'])->name('search');
    Route::get('popular', [ProductController::class, 'popular'])->name('popular');
    Route::get('recent', [ProductController::class, 'recent'])->name('recent');
    Route::get('on-sale', [ProductController::class, 'onSale'])->name('on-sale');
    Route::get('stats', [ProductController::class, 'stats'])->name('stats');
    Route::get('{product}/recommended', [ProductController::class, 'recommended'])->name('recommended');
    Route::post('clear-cache', [ProductController::class, 'clearCache'])->name('clear-cache');
});

// Route per ricerca globale
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $term = $request->get('q');
    
    if (!$term) {
        return redirect()->route('products.index');
    }
    
    // Redirect alla ricerca prodotti per semplicità
    return redirect()->route('products.search', ['q' => $term]);
})->name('search');

// Route per dashboard
Route::get('/dashboard', function () {
    $productStats = app(ProductService::class)->getProductStatistics();
    
    return view('dashboard', compact('productStats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $productStats = app(ProductService::class)->getProductStatistics();
    
    return view('stats', compact('productStats'));
})->name('stats');

// Route per dimostrare il pattern Specification
Route::get('/specification-demo', function () {
    return view('specification-demo');
})->name('specification-demo');
