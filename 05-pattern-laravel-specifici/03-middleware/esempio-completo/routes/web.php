<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
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
    return redirect()->route('blog.index');
});

// Route blog pubbliche (con cache)
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])
        ->middleware('cache:300') // Cache per 5 minuti
        ->name('index');
    
    Route::get('/post/{id}', [BlogController::class, 'show'])
        ->middleware('cache:600') // Cache per 10 minuti
        ->name('show');
    
    // Route per creazione post (richiede autenticazione e ruolo editor)
    Route::middleware(['auth', 'role:editor,admin'])->group(function () {
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/store', [BlogController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BlogController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [BlogController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [BlogController::class, 'destroy'])->name('destroy');
    });
});

// Route admin (richiede autenticazione e ruolo admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/posts', [AdminController::class, 'posts'])->name('posts');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});

// Route API pubbliche (con rate limiting)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/posts', [BlogController::class, 'apiPosts'])
        ->middleware('cache:60') // Cache per 1 minuto
        ->name('posts');
    
    Route::get('/posts/{id}', [BlogController::class, 'apiPost'])
        ->middleware('cache:120') // Cache per 2 minuti
        ->name('post');
    
    // Route API protette (con rate limiting più restrittivo)
    Route::middleware(['auth', 'role:editor,admin', 'rate.limit:30'])->group(function () {
        Route::post('/posts', [BlogController::class, 'apiCreatePost'])->name('posts.create');
        Route::put('/posts/{id}', [BlogController::class, 'apiUpdatePost'])->name('posts.update');
        Route::delete('/posts/{id}', [BlogController::class, 'apiDeletePost'])->name('posts.delete');
    });
});

// Route demo middleware
Route::prefix('middleware-demo')->name('middleware.demo.')->group(function () {
    Route::get('/', function () {
        return view('middleware.demo');
    })->name('index');
    
    Route::get('/test', function () {
        return view('middleware.test');
    })->name('test');
    
    // Test middleware specifici
    Route::get('/auth-test', function () {
        return response()->json([
            'message' => 'Auth middleware passed',
            'user' => auth()->user(),
        ]);
    })->middleware('auth')->name('auth-test');
    
    Route::get('/role-test', function () {
        return response()->json([
            'message' => 'Role middleware passed',
            'user' => auth()->user(),
            'roles' => auth()->user()->roles ?? [],
        ]);
    })->middleware(['auth', 'role:admin'])->name('role-test');
    
    Route::get('/cache-test', function () {
        return response()->json([
            'message' => 'Cache middleware test',
            'timestamp' => now()->toISOString(),
            'random' => rand(1, 1000),
        ]);
    })->middleware('cache:30')->name('cache-test');
    
    Route::get('/rate-limit-test', function () {
        return response()->json([
            'message' => 'Rate limit test',
            'timestamp' => now()->toISOString(),
            'attempts' => request()->header('X-RateLimit-Remaining', 'N/A'),
        ]);
    })->middleware('rate.limit:5')->name('rate-limit-test');
});

// Route per testare i middleware
Route::get('/test-middleware', function () {
    $middleware = [
        'auth' => app()->bound('App\Http\Middleware\AuthMiddleware'),
        'role' => app()->bound('App\Http\Middleware\RoleMiddleware'),
        'cache' => app()->bound('App\Http\Middleware\CacheMiddleware'),
        'log' => app()->bound('App\Http\Middleware\LogMiddleware'),
    ];

    return response()->json([
        'success' => true,
        'message' => 'Middleware Test',
        'data' => $middleware,
        'timestamp' => now()->toISOString(),
    ]);
})->name('test-middleware');

// Route per testare le performance
Route::get('/performance-test', function () {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();
    
    // Simula operazione
    usleep(100000); // 100ms
    
    $endTime = microtime(true);
    $endMemory = memory_get_usage();
    
    return response()->json([
        'success' => true,
        'message' => 'Performance test completed',
        'data' => [
            'execution_time_ms' => round(($endTime - $startTime) * 1000, 2),
            'memory_used_bytes' => $endMemory - $startMemory,
            'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
        ],
        'timestamp' => now()->toISOString(),
    ]);
})->name('performance-test');

// Route per testare la cache
Route::get('/cache-test', function () {
    $key = 'test_cache_' . now()->format('Y-m-d-H-i-s');
    $value = [
        'message' => 'Cache test',
        'timestamp' => now()->toISOString(),
        'random' => rand(1, 1000),
    ];
    
    // Salva in cache
    cache()->put($key, $value, 60);
    
    // Recupera da cache
    $cached = cache()->get($key);
    
    return response()->json([
        'success' => true,
        'message' => 'Cache test completed',
        'data' => [
            'key' => $key,
            'stored' => $value,
            'retrieved' => $cached,
            'match' => $value === $cached,
        ],
        'timestamp' => now()->toISOString(),
    ]);
})->name('cache-test');

// Route per dashboard
Route::get('/dashboard', function () {
    $stats = [
        'total_requests' => cache()->get('total_requests', 0),
        'cache_hits' => cache()->get('cache_hits', 0),
        'cache_misses' => cache()->get('cache_misses', 0),
        'average_response_time' => cache()->get('average_response_time', 0),
    ];
    
    return view('dashboard', compact('stats'));
})->middleware('auth')->name('dashboard');
