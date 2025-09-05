<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servizio di notifica via SMS
 * 
 * Implementa l'invio di notifiche tramite SMS utilizzando
 * un servizio di terze parti (simulato).
 */
class SmsNotificationService implements NotificationServiceInterface
{
    private bool $isAvailable;
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key', '');
        $this->apiUrl = config('services.sms.api_url', 'https://api.sms-provider.com');
        $this->isAvailable = $this->checkAvailability();
    }

    /**
     * Invia una notifica via SMS
     */
    public function send(string $message, string $recipient): bool
    {
        if (!$this->isAvailable) {
            Log::warning('Tentativo di invio SMS con servizio non disponibile', [
                'recipient' => $recipient,
                'message' => $message
            ]);
            return false;
        }

        try {
            // Simula l'invio dell'SMS
            Log::info('SMS inviato con successo', [
                'recipient' => $recipient,
                'message' => $message,
                'service' => 'sms'
            ]);

            // In un'applicazione reale, qui useresti un client HTTP per chiamare l'API
            // $response = Http::post($this->apiUrl . '/send', [
            //     'api_key' => $this->apiKey,
            //     'to' => $recipient,
            //     'message' => $message
            // ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Errore durante l\'invio dell\'SMS', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verifica se il servizio SMS è disponibile
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
        return 'sms';
    }

    /**
     * Ottiene informazioni di debug
     */
    public function getDebugInfo(): array
    {
        return [
            'type' => 'sms',
            'available' => $this->isAvailable,
            'api_url' => $this->apiUrl,
            'api_key_configured' => !empty($this->apiKey),
            'config' => config('services.sms')
        ];
    }

    /**
     * Verifica la disponibilità del servizio SMS
     */
    private function checkAvailability(): bool
    {
        try {
            // Simula un controllo di disponibilità dell'API
            $apiKeyConfigured = !empty($this->apiKey);
            $apiUrlConfigured = !empty($this->apiUrl) && $this->apiUrl !== 'https://api.sms-provider.com';
            
            return $apiKeyConfigured && $apiUrlConfigured;
        } catch (\Exception $e) {
            Log::error('Errore durante il controllo della disponibilità SMS', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
