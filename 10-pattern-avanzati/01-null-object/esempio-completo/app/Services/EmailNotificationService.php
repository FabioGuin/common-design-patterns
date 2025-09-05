<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servizio di notifica via email
 * 
 * Implementa l'invio di notifiche tramite email utilizzando
 * il sistema di mail di Laravel.
 */
class EmailNotificationService implements NotificationServiceInterface
{
    private bool $isAvailable;
    private string $smtpHost;
    private int $smtpPort;

    public function __construct()
    {
        $this->smtpHost = config('mail.mailers.smtp.host', 'localhost');
        $this->smtpPort = config('mail.mailers.smtp.port', 587);
        $this->isAvailable = $this->checkAvailability();
    }

    /**
     * Invia una notifica via email
     */
    public function send(string $message, string $recipient): bool
    {
        if (!$this->isAvailable) {
            Log::warning('Tentativo di invio email con servizio non disponibile', [
                'recipient' => $recipient,
                'message' => $message
            ]);
            return false;
        }

        try {
            // Simula l'invio dell'email
            Log::info('Email inviata con successo', [
                'recipient' => $recipient,
                'message' => $message,
                'service' => 'email'
            ]);

            // In un'applicazione reale, qui useresti Mail::send()
            // Mail::send('emails.notification', ['message' => $message], function($mail) use ($recipient) {
            //     $mail->to($recipient)->subject('Notifica');
            // });

            return true;
        } catch (\Exception $e) {
            Log::error('Errore durante l\'invio dell\'email', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verifica se il servizio email è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Restituisce il tipo di servizio
     */
    public function getType(): string
    {
        return 'email';
    }

    /**
     * Ottiene informazioni di debug
     */
    public function getDebugInfo(): array
    {
        return [
            'type' => 'email',
            'available' => $this->isAvailable,
            'smtp_host' => $this->smtpHost,
            'smtp_port' => $this->smtpPort,
            'config' => config('mail.mailers.smtp')
        ];
    }

    /**
     * Verifica la disponibilità del servizio SMTP
     */
    private function checkAvailability(): bool
    {
        try {
            // Simula un controllo di connettività SMTP
            // In un'applicazione reale, qui faresti un test di connessione
            $smtpConfigured = !empty($this->smtpHost) && $this->smtpHost !== 'localhost';
            return $smtpConfigured;
        } catch (\Exception $e) {
            Log::error('Errore durante il controllo della disponibilità SMTP', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
