<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AIModelRegistry;
use App\Services\AI\AIModelSelector;
use App\Services\AI\AIModelService;
use App\Services\AI\ModelPerformanceTracker;

class AIModelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra il registry dei modelli
        $this->app->singleton(AIModelRegistry::class, function ($app) {
            return new AIModelRegistry();
        });

        // Registra il selettore di modelli
        $this->app->singleton(AIModelSelector::class, function ($app) {
            return new AIModelSelector($app->make(AIModelRegistry::class));
        });

        // Registra il tracker delle performance
        $this->app->singleton(ModelPerformanceTracker::class, function ($app) {
            return new ModelPerformanceTracker();
        });

        // Registra il servizio principale
        $this->app->singleton(AIModelService::class, function ($app) {
            return new AIModelService(
                $app->make(AIModelRegistry::class),
                $app->make(AIModelSelector::class),
                $app->make(ModelPerformanceTracker::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica le configurazioni
        $this->publishes([
            __DIR__.'/../../config/ai_models.php' => config_path('ai_models.php'),
        ], 'ai-models-config');

        // Carica le configurazioni
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ai_models.php', 'ai_models'
        );

        // Registra i comandi Artisan se necessario
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Qui puoi registrare comandi Artisan personalizzati
            ]);
        }
    }
}
