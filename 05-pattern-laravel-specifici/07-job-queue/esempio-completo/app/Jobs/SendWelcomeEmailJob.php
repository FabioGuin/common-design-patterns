<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;
    public $backoff = [10, 30, 60];

    protected User $user;
    protected string $email;

    public function __construct(User $user, string $email)
    {
        $this->user = $user;
        $this->email = $email;
    }

    public function handle(EmailService $emailService): void
    {
        try {
            Log::info('Iniziando invio email di benvenuto', [
                'user_id' => $this->user->id,
                'email' => $this->email
            ]);

            $emailService->sendWelcomeEmail($this->user, $this->email);

            Log::info('Email di benvenuto inviata con successo', [
                'user_id' => $this->user->id,
                'email' => $this->email
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio email di benvenuto', [
                'user_id' => $this->user->id,
                'email' => $this->email,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job di invio email di benvenuto fallito definitivamente', [
            'user_id' => $this->user->id,
            'email' => $this->email,
            'error' => $exception->getMessage()
        ]);
    }
}
