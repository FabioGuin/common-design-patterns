<?php

namespace App\Providers;

use App\Services\Logger\LoggerService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider per il LoggerService
 * Registra il singleton nel Service Container di Laravel
 */
class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi nel container
     */
    public function register(): void
    {
        // Registra il LoggerService come singleton nel container
        $this->app->singleton(LoggerService::class, function ($app) {
            return LoggerService::getInstance();
        });

        // Registra anche un alias per facilità d'uso
        $this->app->alias(LoggerService::class, 'custom.logger');
    }

    /**
     * Avvia i servizi
     */
    public function boot(): void
    {
        // Eventuali configurazioni aggiuntive possono essere aggiunte qui
    }
}
