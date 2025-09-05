<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
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

// Route per utenti
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('{user}', [UserController::class, 'show'])->name('show');
    Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('{user}', [UserController::class, 'update'])->name('update');
    Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    Route::post('{user}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
    Route::post('{user}/change-role', [UserController::class, 'changeRole'])->name('change-role');
    Route::get('role/{role}', [UserController::class, 'byRole'])->name('by-role');
    Route::get('active', [UserController::class, 'active'])->name('active');
    Route::get('most-active', [UserController::class, 'mostActive'])->name('most-active');
    Route::get('search', [UserController::class, 'search'])->name('search');
    Route::get('stats', [UserController::class, 'stats'])->name('stats');
    Route::get('api', [UserController::class, 'api'])->name('api');
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
    $userStats = app(UserService::class)->getUserStats();
    
    return view('dashboard', compact('articleStats', 'userStats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $articleStats = app(ArticleService::class)->getArticleStats();
    $userStats = app(UserService::class)->getUserStats();
    
    return view('stats', compact('articleStats', 'userStats'));
})->name('stats');
