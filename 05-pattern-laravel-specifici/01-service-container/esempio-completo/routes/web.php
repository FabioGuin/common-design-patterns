<?php

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
    return redirect()->route('users.index');
});

// Route per utenti (Resource Controller)
Route::resource('users', UserController::class);

// Route aggiuntive per utenti
Route::prefix('users')->name('users.')->group(function () {
    Route::post('{user}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
    Route::get('search', [UserController::class, 'search'])->name('search');
    Route::get('stats', [UserController::class, 'stats'])->name('stats');
    Route::post('clear-cache', [UserController::class, 'clearCache'])->name('clear-cache');
    Route::get('test-services', [UserController::class, 'testServices'])->name('test-services');
});

// Route per ricerca globale
Route::get('/search', function (\Illuminate\Http\Request $request) {
    $term = $request->get('q');
    
    if (!$term) {
        return redirect()->route('users.index');
    }
    
    // Redirect alla ricerca utenti per semplicità
    return redirect()->route('users.search', ['q' => $term]);
})->name('search');

// Route per dashboard
Route::get('/dashboard', function () {
    $userStats = app(UserService::class)->getUserStats();
    
    return view('dashboard', compact('userStats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $userStats = app(UserService::class)->getUserStats();
    
    return view('stats', compact('userStats'));
})->name('stats');

// Route per dimostrare il pattern Service Container
Route::get('/service-container-demo', function () {
    return view('service-container-demo');
})->name('service-container-demo');

// Route per testare il Service Container
Route::get('/container-test', function () {
    $container = app();
    
    $services = [
        'UserService' => app(UserService::class),
        'EmailService' => app(EmailService::class),
        'CacheService' => app(CacheService::class),
    ];
    
    $info = [];
    foreach ($services as $name => $service) {
        $info[$name] = [
            'class' => get_class($service),
            'is_singleton' => $container->isShared($name),
            'is_bound' => $container->bound($name),
        ];
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Service Container Test',
        'data' => $info
    ]);
})->name('container-test');
