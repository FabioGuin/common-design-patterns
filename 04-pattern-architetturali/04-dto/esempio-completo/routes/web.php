<?php

use App\Http\Controllers\ArticleController;
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
    return redirect()->route('articles.index');
});

// Route per articoli (Resource Controller)
Route::resource('articles', ArticleController::class);

// Route aggiuntive per articoli
Route::prefix('articles')->name('articles.')->group(function () {
    Route::post('{article}/publish', [ArticleController::class, 'publish'])->name('publish');
    Route::post('{article}/draft', [ArticleController::class, 'draft'])->name('draft');
    Route::get('search', [ArticleController::class, 'search'])->name('search');
    Route::get('popular', [ArticleController::class, 'popular'])->name('popular');
    Route::get('stats', [ArticleController::class, 'stats'])->name('stats');
    Route::get('api', [ArticleController::class, 'api'])->name('api');
});

// Route per ricerca globale
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $term = $request->get('q');
    
    if (!$term) {
        return redirect()->route('articles.index');
    }
    
    // Redirect alla ricerca articoli per semplicità
    return redirect()->route('articles.search', ['q' => $term]);
})->name('search');

// Route per dashboard
Route::get('/dashboard', function () {
    $articleStats = app(ArticleService::class)->getArticleStats();
    
    return view('dashboard', compact('articleStats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $articleStats = app(ArticleService::class)->getArticleStats();
    
    return view('stats', compact('articleStats'));
})->name('stats');

// Route per dimostrare il pattern DTO
Route::get('/dto-demo', function () {
    return view('dto-demo');
})->name('dto-demo');
