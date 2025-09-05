<?php

namespace App\Providers;

use App\Services\UserService;
use App\Services\ArticleService;
use App\Services\EmailService;
use App\Services\CacheService;
use App\Repositories\UserRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ArticleRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Log::info('BlogServiceProvider: Registering services');

        // Registra i repository
        $this->registerRepositories();

        // Registra i servizi
        $this->registerServices();

        // Registra i servizi singleton
        $this->registerSingletons();

        Log::info('BlogServiceProvider: Services registered successfully');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Log::info('BlogServiceProvider: Booting services');
    }

    /**
     * Registra i repository
     */
    private function registerRepositories(): void
    {
        // Bind interface to implementation
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);

        Log::debug('BlogServiceProvider: Repositories registered');
    }

    /**
     * Registra i servizi
     */
    private function registerServices(): void
    {
        // UserService con dipendenze
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class),
                $app->make(EmailService::class),
                $app->make(CacheService::class)
            );
        });

        // ArticleService con dipendenze
        $this->app->bind(ArticleService::class, function ($app) {
            return new ArticleService(
                $app->make(ArticleRepositoryInterface::class),
                $app->make(EmailService::class),
                $app->make(CacheService::class)
            );
        });

        Log::debug('BlogServiceProvider: Services registered');
    }

    /**
     * Registra i servizi singleton
     */
    private function registerSingletons(): void
    {
        // EmailService come singleton
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService($app->make('config'));
        });

        // CacheService come singleton
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService($app->make('cache'));
        });

        Log::debug('BlogServiceProvider: Singletons registered');
    }

    /**
     * Verifica se un servizio è registrato
     */
    public function isServiceRegistered(string $service): bool
    {
        return $this->app->bound($service);
    }

    /**
     * Ottiene la lista dei servizi registrati
     */
    public function getRegisteredServices(): array
    {
        return [
            'repositories' => [
                UserRepositoryInterface::class,
                ArticleRepositoryInterface::class,
            ],
            'services' => [
                UserService::class,
                ArticleService::class,
            ],
            'singletons' => [
                EmailService::class,
                CacheService::class,
            ]
        ];
    }

    /**
     * Testa la risoluzione di un servizio
     */
    public function testServiceResolution(string $service): bool
    {
        try {
            $instance = $this->app->make($service);
            return $instance !== null;
        } catch (\Exception $e) {
            Log::error('BlogServiceProvider: Service resolution failed', [
                'service' => $service,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ottiene informazioni sui servizi
     */
    public function getServiceInfo(): array
    {
        $services = $this->getRegisteredServices();
        $info = [];

        foreach ($services['repositories'] as $service) {
            $info['repositories'][$service] = [
                'registered' => $this->isServiceRegistered($service),
                'resolvable' => $this->testServiceResolution($service)
            ];
        }

        foreach ($services['services'] as $service) {
            $info['services'][$service] = [
                'registered' => $this->isServiceRegistered($service),
                'resolvable' => $this->testServiceResolution($service)
            ];
        }

        foreach ($services['singletons'] as $service) {
            $info['singletons'][$service] = [
                'registered' => $this->isServiceRegistered($service),
                'resolvable' => $this->testServiceResolution($service)
            ];
        }

        return $info;
    }
}
