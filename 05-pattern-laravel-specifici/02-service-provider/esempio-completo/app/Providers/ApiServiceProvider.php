<?php

namespace App\Providers;

use App\Services\Api\ApiClient;
use App\Services\Api\ApiResponse;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra ApiClient come singleton
        $this->app->singleton(ApiClient::class, function ($app) {
            return new ApiClient(
                $app['config']['api.base_url'],
                $app['config']['api.key'],
                $app['config']['api.timeout'] ?? 30,
                $app['config']['api.retry_attempts'] ?? 3
            );
        });

        // Registra ApiResponse
        $this->app->bind(ApiResponse::class, function ($app) {
            return new ApiResponse($app['config']['api.response_format'] ?? 'json');
        });

        // Binding di interfacce
        $this->app->bind(
            \App\Contracts\ApiClientInterface::class,
            ApiClient::class
        );

        // Alias per facilità d'uso
        $this->app->alias(ApiClient::class, 'api.client');
        $this->app->alias(ApiResponse::class, 'api.response');

        // Merge configurazione API
        $this->mergeConfigFrom(
            __DIR__.'/../../config/api.php',
            'api'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica configurazioni
        $this->publishes([
            __DIR__.'/../../config/api.php' => config_path('api.php'),
        ], 'api-config');

        // Registra middleware
        $this->app['router']->aliasMiddleware('api.auth', ApiAuthMiddleware::class);
        $this->app['router']->aliasMiddleware('api.rate_limit', 'throttle:api');

        // Configura rate limiting
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute($this->app['config']['api.rate_limit'] ?? 60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Registra route groups API
        Route::middleware(['api', 'api.rate_limit'])
            ->prefix('api/v1')
            ->name('api.v1.')
            ->group(function () {
                Route::get('/posts', [\App\Http\Controllers\ApiController::class, 'posts'])
                    ->name('posts');
                Route::get('/posts/{id}', [\App\Http\Controllers\ApiController::class, 'post'])
                    ->name('post');
                Route::post('/posts', [\App\Http\Controllers\ApiController::class, 'createPost'])
                    ->middleware('api.auth')
                    ->name('posts.create');
                Route::put('/posts/{id}', [\App\Http\Controllers\ApiController::class, 'updatePost'])
                    ->middleware('api.auth')
                    ->name('posts.update');
                Route::delete('/posts/{id}', [\App\Http\Controllers\ApiController::class, 'deletePost'])
                    ->middleware('api.auth')
                    ->name('posts.delete');
            });

        // Route per test API
        Route::middleware(['web'])
            ->prefix('api-demo')
            ->name('api.demo.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\ApiController::class, 'demo'])
                    ->name('index');
                Route::get('/test', [\App\Http\Controllers\ApiController::class, 'test'])
                    ->name('test');
            });

        // Configurazione dinamica per ambiente
        if ($this->app->environment('testing')) {
            $this->app['config']->set('api.timeout', 5);
            $this->app['config']->set('api.retry_attempts', 1);
        }

        // Registra macro per response API
        \Illuminate\Http\Response::macro('api', function ($data = null, $message = 'Success', $status = 200) {
            return response()->json([
                'success' => $status >= 200 && $status < 300,
                'message' => $message,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ], $status);
        });

        // Registra macro per errori API
        \Illuminate\Http\Response::macro('apiError', function ($message = 'Error', $status = 400, $errors = null) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
                'timestamp' => now()->toISOString(),
            ], $status);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ApiClient::class,
            ApiResponse::class,
            'api.client',
            'api.response',
        ];
    }
}
