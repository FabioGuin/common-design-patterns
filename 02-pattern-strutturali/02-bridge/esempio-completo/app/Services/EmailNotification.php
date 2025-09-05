<?php

namespace App\Services;

class EmailNotification extends NotificationAbstract
{
    /**
     * Invia una notifica via email
     */
    public function send(string $message, array $data = []): array
    {
        try {
            if (!$this->isAvailable()) {
                throw new \Exception('Email service not available');
            }

            $formattedMessage = $this->formatMessage($message, $data);
            $recipient = $data['email'] ?? 'user@example.com';
            $subject = $data['title'] ?? 'Notifica';

            // Simula l'invio dell'email
            $this->simulateEmailSend($recipient, $subject, $formattedMessage);

            $result = [
                'success' => true,
                'channel' => $this->getChannel(),
                'formatter' => $this->formatter->getType(),
                'recipient' => $recipient,
                'subject' => $subject,
                'message_id' => uniqid('email_', true),
            ];

            $this->logNotification($message, $data, true);
            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'channel' => $this->getChannel(),
                'formatter' => $this->formatter->getType(),
                'error' => $e->getMessage(),
            ];

            $this->logNotification($message, $data, false);
            return $result;
        }
    }

    /**
     * Ottiene il canale di notifica
     */
    public function getChannel(): string
    {
        return 'email';
    }

    /**
     * Verifica se il canale è disponibile
     */
    public function isAvailable(): bool
    {
        // In un'implementazione reale, verificheresti la configurazione email
        return config('mail.default') !== null;
    }

    /**
     * Simula l'invio di un'email
     */
    private function simulateEmailSend(string $recipient, string $subject, string $body): void
    {
        // In un'implementazione reale, useresti Mail::send() o simile
        \Log::info('Email sent', [
            'to' => $recipient,
            'subject' => $subject,
            'body_length' => strlen($body),
        ]);
    }
}
