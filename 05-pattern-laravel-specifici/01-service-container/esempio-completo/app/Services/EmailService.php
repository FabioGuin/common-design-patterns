<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(
        private array $config
    ) {}

    /**
     * Invia email di benvenuto
     */
    public function sendWelcomeEmail(User $user): void
    {
        Log::info('EmailService: Sending welcome email', ['user_id' => $user->id]);

        try {
            Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Benvenuto nella nostra piattaforma!');
            });

            Log::info('EmailService: Welcome email sent successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia email di attivazione
     */
    public function sendActivationEmail(User $user): void
    {
        Log::info('EmailService: Sending activation email', ['user_id' => $user->id]);

        try {
            Mail::send('emails.activation', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Il tuo account è stato attivato!');
            });

            Log::info('EmailService: Activation email sent successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Failed to send activation email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia email di notifica
     */
    public function sendNotificationEmail(User $user, string $subject, string $template, array $data = []): void
    {
        Log::info('EmailService: Sending notification email', [
            'user_id' => $user->id,
            'subject' => $subject,
            'template' => $template
        ]);

        try {
            $data['user'] = $user;
            
            Mail::send($template, $data, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                        ->subject($subject);
            });

            Log::info('EmailService: Notification email sent successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Failed to send notification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia email di reset password
     */
    public function sendPasswordResetEmail(User $user, string $token): void
    {
        Log::info('EmailService: Sending password reset email', ['user_id' => $user->id]);

        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'token' => $token,
                'resetUrl' => url("/password/reset/{$token}")
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Reset della password');
            });

            Log::info('EmailService: Password reset email sent successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Failed to send password reset email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia email di verifica
     */
    public function sendVerificationEmail(User $user, string $verificationToken): void
    {
        Log::info('EmailService: Sending verification email', ['user_id' => $user->id]);

        try {
            Mail::send('emails.verification', [
                'user' => $user,
                'verificationUrl' => url("/verify/{$verificationToken}")
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Verifica il tuo indirizzo email');
            });

            Log::info('EmailService: Verification email sent successfully', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('EmailService: Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia email bulk
     */
    public function sendBulkEmail(array $users, string $subject, string $template, array $data = []): int
    {
        Log::info('EmailService: Sending bulk email', [
            'user_count' => count($users),
            'subject' => $subject,
            'template' => $template
        ]);

        $sentCount = 0;

        foreach ($users as $user) {
            try {
                $this->sendNotificationEmail($user, $subject, $template, $data);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('EmailService: Failed to send bulk email to user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('EmailService: Bulk email completed', [
            'total_users' => count($users),
            'sent_count' => $sentCount
        ]);

        return $sentCount;
    }

    /**
     * Verifica la configurazione email
     */
    public function isEmailConfigured(): bool
    {
        return !empty($this->config['mail']['from']['address']);
    }

    /**
     * Ottiene le impostazioni email
     */
    public function getEmailSettings(): array
    {
        return [
            'from_address' => $this->config['mail']['from']['address'] ?? null,
            'from_name' => $this->config['mail']['from']['name'] ?? null,
            'driver' => $this->config['mail']['default'] ?? null,
            'is_configured' => $this->isEmailConfigured()
        ];
    }

    /**
     * Testa la connessione email
     */
    public function testEmailConnection(): bool
    {
        try {
            // Test semplice inviando un'email di test
            Mail::raw('Test email', function ($message) {
                $message->to($this->config['mail']['from']['address'])
                        ->subject('Test Email Connection');
            });

            return true;
        } catch (\Exception $e) {
            Log::error('EmailService: Email connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
