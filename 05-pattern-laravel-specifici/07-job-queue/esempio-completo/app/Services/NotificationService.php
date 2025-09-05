<?php

namespace App\Services;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function sendNotification(User $user, string $title, string $message, array $data = []): void
    {
        try {
            // Invia notifica push (simulata)
            $this->sendPushNotification($user, $title, $message, $data);

            // Invia email di notifica
            $this->emailService->sendNotificationEmail($user, $title, $message, $data);

            Log::info('Notifica inviata con successo', [
                'user_id' => $user->id,
                'title' => $title,
                'channels' => ['push', 'email']
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio notifica', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function sendPushNotification(User $user, string $title, string $message, array $data = []): void
    {
        // Simulazione invio push notification
        // In un'applicazione reale, qui useresti servizi come Firebase, OneSignal, etc.
        
        Log::info('Push notification inviata', [
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);

        // Simula un delay per l'invio push
        usleep(100000); // 100ms
    }

    public function sendBulkNotification(array $users, string $title, string $message, array $data = []): void
    {
        foreach ($users as $user) {
            try {
                $this->sendNotification($user, $title, $message, $data);
            } catch (\Exception $e) {
                Log::error('Errore nell\'invio notifica bulk', [
                    'user_id' => $user->id,
                    'title' => $title,
                    'error' => $e->getMessage()
                ]);
                // Continua con gli altri utenti anche se uno fallisce
            }
        }
    }
}
