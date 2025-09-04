<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Payment\Factories\PaymentFactory;
use App\Services\Payment\Factories\StripePaymentFactory;
use App\Services\Payment\Factories\PayPalPaymentFactory;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentFactory::class, function ($app) {
            $provider = config('payment.default_provider', 'stripe');
            
            return match($provider) {
                'stripe' => new StripePaymentFactory(
                    config('payment.stripe.api_key'),
                    config('payment.stripe.webhook_secret')
                ),
                'paypal' => new PayPalPaymentFactory(
                    config('payment.paypal.client_id'),
                    config('payment.paypal.client_secret')
                ),
                default => throw new \InvalidArgumentException("Unsupported payment provider: {$provider}")
            };
        });
    }
    
    public function boot(): void
    {
        // Pubblica la configurazione se necessario
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/payment.php' => config_path('payment.php'),
            ], 'payment-config');
        }
    }
}

