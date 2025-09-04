<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UserFactory\UserFactoryInterface;
use App\Services\UserFactory\AdminUserFactory;
use App\Services\UserFactory\RegularUserFactory;
use App\Services\UserFactory\GuestUserFactory;

class UserFactoryServiceProvider extends ServiceProvider
{
    /**
     * Registra i servizi dell'applicazione
     */
    public function register(): void
    {
        // Registra le factory nel container
        $this->app->bind(AdminUserFactory::class, function ($app) {
            return new AdminUserFactory();
        });

        $this->app->bind(RegularUserFactory::class, function ($app) {
            return new RegularUserFactory();
        });

        $this->app->bind(GuestUserFactory::class, function ($app) {
            return new GuestUserFactory();
        });

        // Registra un resolver per ottenere la factory corretta
        $this->app->bind(UserFactoryInterface::class, function ($app, $parameters) {
            $userType = $parameters['userType'] ?? 'user';
            
            return match($userType) {
                'admin' => $app->make(AdminUserFactory::class),
                'user' => $app->make(RegularUserFactory::class),
                'guest' => $app->make(GuestUserFactory::class),
                default => $app->make(RegularUserFactory::class)
            };
        });
    }

    /**
     * Avvia i servizi dell'applicazione
     */
    public function boot(): void
    {
        //
    }
}
