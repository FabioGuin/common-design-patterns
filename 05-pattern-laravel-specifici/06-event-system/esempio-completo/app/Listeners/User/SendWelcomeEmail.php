<?php

namespace App\Listeners\User;

use App\Events\User\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            // Simula invio email di benvenuto
            Log::info('Sending welcome email to user', [
                'user_id' => $event->user->id,
                'user_email' => $event->user->email,
                'user_name' => $event->user->name
            ]);

            // In un'applicazione reale, useresti:
            // Mail::to($event->user->email)->send(new WelcomeEmail($event->user));

            // Simula delay per dimostrare l'asincrono
            sleep(1);

            Log::info('Welcome email sent successfully', [
                'user_id' => $event->user->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage()
            ]);

            // Re-throw per far fallire il job se necessario
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('Welcome email job failed', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
