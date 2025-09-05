<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendWelcomeEmail(User $user, string $email): void
    {
        try {
            $data = [
                'user' => $user,
                'name' => $user->name,
                'email' => $email,
                'welcome_message' => 'Benvenuto nella nostra piattaforma!'
            ];

            Mail::send('emails.welcome', $data, function ($message) use ($email, $user) {
                $message->to($email, $user->name)
                        ->subject('Benvenuto nella nostra piattaforma!');
            });

            Log::info('Email di benvenuto inviata', [
                'user_id' => $user->id,
                'email' => $email
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio email di benvenuto', [
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function sendNewsletter(User $user, array $newsletterData): void
    {
        try {
            $data = [
                'user' => $user,
                'name' => $user->name,
                'newsletter' => $newsletterData,
                'unsubscribe_url' => route('newsletter.unsubscribe', $user->id)
            ];

            Mail::send('emails.newsletter', $data, function ($message) use ($user, $newsletterData) {
                $message->to($user->email, $user->name)
                        ->subject($newsletterData['subject'] ?? 'Newsletter');
            });

            Log::info('Newsletter inviata', [
                'user_id' => $user->id,
                'newsletter_id' => $newsletterData['id'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio newsletter', [
                'user_id' => $user->id,
                'newsletter_id' => $newsletterData['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function sendNotificationEmail(User $user, string $title, string $message, array $data = []): void
    {
        try {
            $emailData = [
                'user' => $user,
                'name' => $user->name,
                'title' => $title,
                'message' => $message,
                'data' => $data
            ];

            Mail::send('emails.notification', $emailData, function ($message) use ($user, $title) {
                $message->to($user->email, $user->name)
                        ->subject($title);
            });

            Log::info('Email di notifica inviata', [
                'user_id' => $user->id,
                'title' => $title
            ]);

        } catch (\Exception $e) {
            Log::error('Errore nell\'invio email di notifica', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
