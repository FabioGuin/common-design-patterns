<?php

namespace App\Providers;

use App\Events\User\UserLoggedIn;
use App\Events\User\UserRegistered;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderPaid;
use App\Listeners\User\SendWelcomeEmail;
use App\Listeners\User\LogUserActivity;
use App\Listeners\Order\SendOrderConfirmation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // User Events
        UserRegistered::class => [
            SendWelcomeEmail::class,
            LogUserActivity::class,
        ],

        UserLoggedIn::class => [
            LogUserActivity::class,
        ],

        // Order Events
        OrderCreated::class => [
            SendOrderConfirmation::class,
        ],

        OrderPaid::class => [
            SendOrderConfirmation::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
