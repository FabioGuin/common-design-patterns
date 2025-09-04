<?php

namespace App\Providers;

use App\Services\PoolManager;
use App\Services\ConnectionPool;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class PoolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registra il PoolManager come singleton
        $this->app->singleton(PoolManager::class, function ($app) {
            return PoolManager::getInstance();
        });

        // Crea il pool di default se configurato
        $this->app->afterResolving(PoolManager::class, function (PoolManager $poolManager) {
            $this->createDefaultPools($poolManager);
        });
    }

    public function boot(): void
    {
        // Pubblica la configurazione se necessario
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/pool.php' => config_path('pool.php'),
            ], 'pool-config');
        }
    }

    private function createDefaultPools(PoolManager $poolManager): void
    {
        try {
            // Crea il pool di default per MySQL
            if (config('database.default') === 'mysql') {
                $poolManager->createPool(
                    'default',
                    'mysql',
                    config('pool.default.max_size', 10),
                    config('pool.default.timeout', 30),
                    config('pool.default.retry_attempts', 3)
                );
                
                Log::info('Pool di default creato per MySQL');
            }

            // Crea pool aggiuntivi se configurati
            $additionalPools = config('pool.additional_pools', []);
            foreach ($additionalPools as $poolConfig) {
                $poolManager->createPool(
                    $poolConfig['name'],
                    $poolConfig['connection'],
                    $poolConfig['max_size'] ?? 10,
                    $poolConfig['timeout'] ?? 30,
                    $poolConfig['retry_attempts'] ?? 3
                );
                
                Log::info("Pool aggiuntivo '{$poolConfig['name']}' creato");
            }

        } catch (\Exception $e) {
            Log::error("Errore creazione pool di default: " . $e->getMessage());
        }
    }
}
