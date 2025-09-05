<?php

use App\Http\Controllers\BlogController;
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

// Route blog pubbliche
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/post/{id}', [BlogController::class, 'show'])->name('show');
    
    // Route per creazione post (richiede autenticazione)
    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/store', [BlogController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BlogController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [BlogController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [BlogController::class, 'destroy'])->name('destroy');
    });
});

// Route API pubbliche
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/posts', [BlogController::class, 'apiPosts'])->name('posts');
    Route::get('/posts/{id}', [BlogController::class, 'apiPost'])->name('post');
    Route::get('/stats', [BlogController::class, 'apiStats'])->name('stats');
    
    // Route API protette
    Route::middleware(['api.auth'])->group(function () {
        Route::post('/posts', [BlogController::class, 'apiCreatePost'])->name('posts.create');
        Route::put('/posts/{id}', [BlogController::class, 'apiUpdatePost'])->name('posts.update');
        Route::delete('/posts/{id}', [BlogController::class, 'apiDeletePost'])->name('posts.delete');
    });
});

// Route demo API
Route::prefix('api-demo')->name('api.demo.')->group(function () {
    Route::get('/', [ApiController::class, 'demo'])->name('index');
    Route::get('/test', [ApiController::class, 'test'])->name('test');
    Route::get('/channels', [ApiController::class, 'channels'])->name('channels');
    Route::get('/notifications', [ApiController::class, 'notifications'])->name('notifications');
});

// Route per testare i Service Provider
Route::get('/service-provider-demo', function () {
    return view('service-provider-demo');
})->name('service-provider-demo');

// Route per testare i servizi
Route::get('/test-services', function () {
    $services = [
        'PostService' => app(\App\Services\Blog\PostService::class),
        'CommentService' => app(\App\Services\Blog\CommentService::class),
        'CategoryService' => app(\App\Services\Blog\CategoryService::class),
        'ApiClient' => app(\App\Services\Api\ApiClient::class),
        'NotificationService' => app(\App\Services\Notification\NotificationService::class),
    ];

    $results = [];
    foreach ($services as $name => $service) {
        $results[$name] = [
            'class' => get_class($service),
            'is_singleton' => app()->isShared(get_class($service)),
            'is_bound' => app()->bound(get_class($service)),
            'config' => method_exists($service, 'getConfig') ? $service->getConfig() : null,
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Service Provider Test',
        'data' => $results
    ]);
})->name('test-services');

// Route per testare le notifiche
Route::get('/test-notifications', function () {
    $notificationService = app(\App\Services\Notification\NotificationService::class);
    
    $results = [
        'channels' => $notificationService->getAllChannels(),
        'enabled_channels' => $notificationService->getEnabledChannels(),
        'stats' => $notificationService->getStats(),
        'test_results' => $notificationService->testChannels(),
    ];

    return response()->json([
        'success' => true,
        'message' => 'Notification Service Test',
        'data' => $results
    ]);
})->name('test-notifications');

// Route per testare l'API Client
Route::get('/test-api-client', function () {
    $apiClient = app(\App\Services\Api\ApiClient::class);
    
    $results = [
        'config' => $apiClient->getConfig(),
        'connection_test' => $apiClient->testConnection(),
        'api_info' => $apiClient->getApiInfo(),
        'rate_limit_info' => $apiClient->getRateLimitInfo(),
    ];

    return response()->json([
        'success' => true,
        'message' => 'API Client Test',
        'data' => $results
    ]);
})->name('test-api-client');

// Route per dashboard
Route::get('/dashboard', function () {
    $postService = app(\App\Services\Blog\PostService::class);
    $notificationService = app(\App\Services\Notification\NotificationService::class);
    
    $stats = [
        'posts' => $postService->getStats(),
        'notifications' => $notificationService->getStats(),
    ];
    
    return view('dashboard', compact('stats'));
})->name('dashboard');

// Route per statistiche globali
Route::get('/stats', function () {
    $postService = app(\App\Services\Blog\PostService::class);
    $notificationService = app(\App\Services\Notification\NotificationService::class);
    
    $stats = [
        'posts' => $postService->getStats(),
        'notifications' => $notificationService->getStats(),
    ];
    
    return view('stats', compact('stats'));
})->name('stats');
