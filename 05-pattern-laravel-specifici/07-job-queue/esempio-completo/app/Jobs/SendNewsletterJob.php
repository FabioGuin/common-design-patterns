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

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [30, 60, 120];

    protected User $user;
    protected array $newsletterData;

    public function __construct(User $user, array $newsletterData)
    {
        $this->user = $user;
        $this->newsletterData = $newsletterData;
    }

    public function handle(EmailService $emailService): void
    {
        try {
            Log::info('Iniziando invio newsletter', [
                'user_id' => $this->user->id,
                'newsletter_id' => $this->newsletterData['id'] ?? 'unknown'
            ]);

            $emailService->sendNewsletter($this->user, $this->newsletterData);

            Log::info('Newsletter inviata con successo', [
                'user_id' => $this->user->id,
                'newsletter_id' => $this->newsletterData['id'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio newsletter', [
                'user_id' => $this->user->id,
                'newsletter_id' => $this->newsletterData['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job di invio newsletter fallito definitivamente', [
            'user_id' => $this->user->id,
            'newsletter_id' => $this->newsletterData['id'] ?? 'unknown',
            'error' => $exception->getMessage()
        ]);
    }
}
