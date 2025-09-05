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
    Route::get('author/{user}', [ArticleController::class, 'byAuthor'])->name('by-author');
    Route::get('category/{category}', [ArticleController::class, 'byCategory'])->name('by-category');
    Route::get('api', [ArticleController::class, 'api'])->name('api');
});

// Route per utenti
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('{user}', [UserController::class, 'show'])->name('show');
    Route::get('{user}/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('{user}/articles', [UserController::class, 'articles'])->name('articles');
    Route::post('{user}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
    Route::post('{user}/change-role', [UserController::class, 'changeRole'])->name('change-role');
    Route::get('api', [UserController::class, 'api'])->name('api');
    Route::get('stats', [UserController::class, 'stats'])->name('stats');
});

// Route per dashboard (se implementata)
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Route per statistiche (se implementate)
Route::get('/stats', function () {
    $stats = [
        'total_articles' => \App\Models\Article::count(),
        'published_articles' => \App\Models\Article::published()->count(),
        'draft_articles' => \App\Models\Article::draft()->count(),
        'total_users' => \App\Models\User::count(),
        'active_users' => \App\Models\User::active()->count(),
    ];
    
    return view('stats', compact('stats'));
})->name('stats');

// Route per ricerca globale (se implementata)
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $query = $request->get('q');
    
    if (!$query) {
        return redirect()->route('articles.index');
    }
    
    $articles = \App\Models\Article::with('user')
                                 ->search($query)
                                 ->published()
                                 ->recent()
                                 ->paginate(10);
    
    return view('search', compact('articles', 'query'));
})->name('search');

// Route per feed RSS (se implementato)
Route::get('/feed', function () {
    $articles = \App\Models\Article::with('user')
                                 ->published()
                                 ->recent()
                                 ->limit(20)
                                 ->get();
    
    return response()->view('feed.rss', compact('articles'))
                   ->header('Content-Type', 'application/rss+xml');
})->name('feed');

// Route per sitemap (se implementata)
Route::get('/sitemap', function () {
    $articles = \App\Models\Article::published()
                                 ->select('id', 'updated_at')
                                 ->get();
    
    return response()->view('sitemap', compact('articles'))
                   ->header('Content-Type', 'application/xml');
})->name('sitemap');
