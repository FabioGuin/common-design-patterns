<?php

namespace App\Providers;

use App\Services\Notification\NotificationService;
use App\Services\Notification\Channels\EmailChannel;
use App\Services\Notification\Channels\SmsChannel;
use App\Services\Notification\Channels\PushChannel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra canali di notifica
        $this->app->singleton(EmailChannel::class, function ($app) {
            return new EmailChannel(
                $app['config']['notifications.email.enabled'] ?? true,
                $app['config']['mail']
            );
        });

        $this->app->singleton(SmsChannel::class, function ($app) {
            return new SmsChannel(
                $app['config']['notifications.sms.enabled'] ?? false,
                $app['config']['sms']
            );
        });

        $this->app->singleton(PushChannel::class, function ($app) {
            return new PushChannel(
                $app['config']['notifications.push.enabled'] ?? true,
                $app['config']['push']
            );
        });

        // Registra NotificationService
        $this->app->singleton(NotificationService::class, function ($app) {
            $channels = collect([
                $app->make(EmailChannel::class),
                $app->make(SmsChannel::class),
                $app->make(PushChannel::class),
            ])->filter(function ($channel) {
                return $channel->isEnabled();
            })->toArray();

            return new NotificationService($channels);
        });

        // Binding di interfacce
        $this->app->bind(
            \App\Contracts\NotificationServiceInterface::class,
            NotificationService::class
        );

        // Alias per facilità d'uso
        $this->app->alias(NotificationService::class, 'notifications');
        $this->app->alias(EmailChannel::class, 'notifications.email');
        $this->app->alias(SmsChannel::class, 'notifications.sms');
        $this->app->alias(PushChannel::class, 'notifications.push');

        // Merge configurazione notifiche
        $this->mergeConfigFrom(
            __DIR__.'/../../config/notifications.php',
            'notifications'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pubblica configurazioni
        $this->publishes([
            __DIR__.'/../../config/notifications.php' => config_path('notifications.php'),
        ], 'notifications-config');

        // Registra canali personalizzati per Laravel Notification
        Notification::extend('email_custom', function ($app) {
            return $app->make(EmailChannel::class);
        });

        Notification::extend('sms_custom', function ($app) {
            return $app->make(SmsChannel::class);
        });

        Notification::extend('push_custom', function ($app) {
            return $app->make(PushChannel::class);
        });

        // Configurazione dinamica per ambiente
        if ($this->app->environment('local')) {
            // In locale, disabilita SMS e Push per default
            $this->app['config']->set('notifications.sms.enabled', false);
            $this->app['config']->set('notifications.push.enabled', false);
        }

        if ($this->app->environment('testing')) {
            // In testing, usa solo email
            $this->app['config']->set('notifications.sms.enabled', false);
            $this->app['config']->set('notifications.push.enabled', false);
            $this->app['config']->set('notifications.email.enabled', true);
        }

        // Registra macro per notifiche
        \Illuminate\Support\Facades\Notification::macro('sendToChannels', function ($notifiable, $notification, $channels = null) {
            $service = app(NotificationService::class);
            return $service->send($notifiable, $notification, $channels);
        });

        // Registra eventi per notifiche
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\NotificationSent::class,
            \App\Listeners\LogNotificationSent::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\NotificationFailed::class,
            \App\Listeners\LogNotificationFailed::class
        );

        // Registra comandi Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\TestNotificationsCommand::class,
                \App\Console\Commands\SendBulkNotificationsCommand::class,
                \App\Console\Commands\CleanupFailedNotificationsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            NotificationService::class,
            EmailChannel::class,
            SmsChannel::class,
            PushChannel::class,
            'notifications',
            'notifications.email',
            'notifications.sms',
            'notifications.push',
        ];
    }
}
