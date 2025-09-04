<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AICacheService;
use App\Services\AI\CacheStrategyManager;
use App\Services\AI\CacheInvalidationService;
use App\Services\AI\CacheWarmingService;
use App\Services\AI\CacheAnalyticsService;

class AICacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra il manager delle strategie
        $this->app->singleton(CacheStrategyManager::class, function ($app) {
            return new CacheStrategyManager();
        });

        // Registra il servizio di invalidazione
        $this->app->singleton(CacheInvalidationService::class, function ($app) {
            return new CacheInvalidationService($app->make(CacheStrategyManager::class));
        });

        // Registra il servizio di pre-riscaldamento
        $this->app->singleton(CacheWarmingService::class, function ($app) {
            return new CacheWarmingService($app->make(AICacheService::class));
        });

        // Registra il servizio di analytics
        $this->app->singleton(CacheAnalyticsService::class, function ($app) {
            return new CacheAnalyticsService();
        });

        // Registra il servizio principale della cache
        $this->app->singleton(AICacheService::class, function ($app) {
            return new AICacheService(
                $app->make(CacheStrategyManager::class),
                $app->make(CacheInvalidationService::class),
                $app->make(CacheWarmingService::class),
                $app->make(CacheAnalyticsService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica la configurazione
        $this->publishes([
            __DIR__ . '/../../config/ai_cache.php' => config_path('ai_cache.php'),
        ], 'ai-cache-config');

        // Pubblica le migrazioni
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'ai-cache-migrations');

        // Carica la configurazione
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ai_cache.php', 'ai_cache'
        );

        // Registra i comandi Artisan se necessario
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Comandi personalizzati per la cache AI
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AICacheService::class,
            CacheStrategyManager::class,
            CacheInvalidationService::class,
            CacheWarmingService::class,
            CacheAnalyticsService::class,
        ];
    }
}
