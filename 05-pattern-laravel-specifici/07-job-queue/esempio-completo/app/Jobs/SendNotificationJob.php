<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 90;
    public $tries = 3;
    public $backoff = [15, 30, 60];

    protected User $user;
    protected string $title;
    protected string $message;
    protected array $data;

    public function __construct(User $user, string $title, string $message, array $data = [])
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    public function handle(NotificationService $notificationService): void
    {
        try {
            Log::info('Iniziando invio notifica', [
                'user_id' => $this->user->id,
                'title' => $this->title
            ]);

            $notificationService->sendNotification(
                $this->user,
                $this->title,
                $this->message,
                $this->data
            );

            Log::info('Notifica inviata con successo', [
                'user_id' => $this->user->id,
                'title' => $this->title
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio notifica', [
                'user_id' => $this->user->id,
                'title' => $this->title,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job di invio notifica fallito definitivamente', [
            'user_id' => $this->user->id,
            'title' => $this->title,
            'error' => $exception->getMessage()
        ]);
    }
}
