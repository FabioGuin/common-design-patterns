<?php

namespace App\Listeners\User;

use App\Events\User\UserLoggedIn;
use App\Events\User\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LogUserActivity implements ShouldQueue
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
     * Handle the user registered event.
     */
    public function handleUserRegistered(UserRegistered $event): void
    {
        $this->logActivity(
            $event->user->id,
            'user_registered',
            'User registered successfully',
            [
                'user_email' => $event->user->email,
                'user_name' => $event->user->name,
                'registration_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => $event->metadata
            ]
        );
    }

    /**
     * Handle the user logged in event.
     */
    public function handleUserLoggedIn(UserLoggedIn $event): void
    {
        $this->logActivity(
            $event->user->id,
            'user_logged_in',
            'User logged in successfully',
            [
                'user_email' => $event->user->email,
                'user_name' => $event->user->name,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'metadata' => $event->metadata
            ]
        );
    }

    /**
     * Log user activity to database.
     */
    private function logActivity(int $userId, string $action, string $description, array $data = []): void
    {
        try {
            DB::table('user_activities')->insert([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'data' => json_encode($data),
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('User activity logged', [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log user activity', [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('User activity logging failed', [
            'event' => get_class($event),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
