<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AIGatewayService;
use App\Services\AI\RateLimiter;
use App\Services\AI\CacheService;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra RateLimiter
        $this->app->singleton(RateLimiter::class, function ($app) {
            return new RateLimiter();
        });

        // Registra CacheService
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        // Registra AIGatewayService
        $this->app->singleton(AIGatewayService::class, function ($app) {
            return new AIGatewayService(
                $app->make(RateLimiter::class),
                $app->make(CacheService::class)
            );
        });

        // Registra provider AI
        $this->registerAIProviders();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica configurazione
        $this->publishes([
            __DIR__ . '/../../config/ai.php' => config_path('ai.php'),
        ], 'ai-gateway-config');

        // Pubblica migrazioni
        $this->publishes([
            __DIR__ . '/../../database/migrations/2024_01_01_000000_create_ai_requests_table.php' => 
                database_path('migrations/2024_01_01_000000_create_ai_requests_table.php'),
        ], 'ai-gateway-migrations');

        // Carica configurazione
        $this->mergeConfigFrom(__DIR__ . '/../../config/ai.php', 'ai');
    }

    /**
     * Registra i provider AI
     */
    private function registerAIProviders(): void
    {
        $providers = config('ai.providers', []);

        foreach ($providers as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $this->app->bind("ai.provider.{$name}", function ($app) use ($name, $config) {
                return match ($name) {
                    'openai' => new OpenAIProvider($config),
                    'claude' => new ClaudeProvider($config),
                    'gemini' => new GeminiProvider($config),
                    default => throw new \InvalidArgumentException("Provider {$name} non supportato")
                };
            });
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AIGatewayService::class,
            RateLimiter::class,
            CacheService::class,
        ];
    }
}
