<?php

namespace App\Services;

class SMSNotification extends NotificationAbstract
{
    /**
     * Invia una notifica via SMS
     */
    public function send(string $message, array $data = []): array
    {
        try {
            if (!$this->isAvailable()) {
                throw new \Exception('SMS service not available');
            }

            $formattedMessage = $this->formatMessage($message, $data);
            $phoneNumber = $data['phone'] ?? '+1234567890';

            // Simula l'invio dell'SMS
            $this->simulateSMSSend($phoneNumber, $formattedMessage);

            $result = [
                'success' => true,
                'channel' => $this->getChannel(),
                'formatter' => $this->formatter->getType(),
                'recipient' => $phoneNumber,
                'message_id' => uniqid('sms_', true),
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
        return 'sms';
    }

    /**
     * Verifica se il canale è disponibile
     */
    public function isAvailable(): bool
    {
        // In un'implementazione reale, verificheresti la configurazione SMS
        return config('services.sms.enabled', true);
    }

    /**
     * Simula l'invio di un SMS
     */
    private function simulateSMSSend(string $phoneNumber, string $message): void
    {
        // In un'implementazione reale, useresti un servizio SMS come Twilio
        \Log::info('SMS sent', [
            'to' => $phoneNumber,
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 50) . '...',
        ]);
    }
}
