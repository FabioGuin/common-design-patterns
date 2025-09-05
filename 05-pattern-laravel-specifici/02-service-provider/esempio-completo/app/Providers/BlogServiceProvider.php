<?php

namespace App\Providers;

use App\Services\Blog\PostService;
use App\Services\Blog\CommentService;
use App\Services\Blog\CategoryService;
use App\Http\Middleware\BlogAuthMiddleware;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra servizi blog come singleton
        $this->app->singleton(PostService::class, function ($app) {
            return new PostService(
                $app['config']['blog.cache_ttl'] ?? 3600,
                $app['config']['blog.per_page'] ?? 15
            );
        });

        $this->app->singleton(CommentService::class, function ($app) {
            return new CommentService(
                $app['config']['blog.features.comments'] ?? true
            );
        });

        $this->app->singleton(CategoryService::class, function ($app) {
            return new CategoryService(
                $app['config']['blog.features.categories'] ?? true
            );
        });

        // Binding di interfacce (esempio)
        $this->app->bind(
            \App\Contracts\PostRepositoryInterface::class,
            \App\Repositories\EloquentPostRepository::class
        );

        // Registra alias per facilità d'uso
        $this->app->alias(PostService::class, 'blog.posts');
        $this->app->alias(CommentService::class, 'blog.comments');
        $this->app->alias(CategoryService::class, 'blog.categories');

        // Merge configurazione blog
        $this->mergeConfigFrom(
            __DIR__.'/../../config/blog.php',
            'blog'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica configurazioni
        $this->publishes([
            __DIR__.'/../../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');

        // Pubblica migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'blog-migrations');

        // Pubblica views
        $this->publishes([
            __DIR__.'/../../resources/views/blog' => resource_path('views/blog'),
        ], 'blog-views');

        // Carica views
        $this->loadViewsFrom(__DIR__.'/../../resources/views/blog', 'blog');

        // Carica traduzioni
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'blog');

        // Registra middleware
        $this->app['router']->aliasMiddleware('blog.auth', BlogAuthMiddleware::class);

        // Registra route groups
        Route::middleware(['web', 'blog.auth'])
            ->prefix('admin/blog')
            ->name('admin.blog.')
            ->group(function () {
                Route::get('/posts', [\App\Http\Controllers\BlogController::class, 'admin'])
                    ->name('posts');
                Route::get('/comments', [\App\Http\Controllers\BlogController::class, 'comments'])
                    ->name('comments');
            });

        // Registra eventi
        Event::listen(\App\Events\PostCreated::class, \App\Listeners\SendPostNotification::class);
        Event::listen(\App\Events\CommentCreated::class, \App\Listeners\SendCommentNotification::class);

        // Registra comandi Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PublishPostsCommand::class,
                \App\Console\Commands\CleanupCommentsCommand::class,
                \App\Console\Commands\GenerateBlogStatsCommand::class,
            ]);
        }

        // Configurazione dinamica basata sull'ambiente
        if ($this->app->environment('production')) {
            $this->app['config']->set('blog.cache_ttl', 7200); // 2 ore in produzione
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PostService::class,
            CommentService::class,
            CategoryService::class,
            'blog.posts',
            'blog.comments',
            'blog.categories',
        ];
    }
}
