<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Factory per la creazione dei servizi di notifica
 * 
 * Implementa il Factory Pattern per creare l'istanza appropriata
 * del servizio di notifica basata sulla configurazione.
 * Utilizza il Null Object Pattern come fallback quando i servizi
 * non sono disponibili o configurati.
 */
class NotificationServiceFactory
{
    /**
     * Crea un'istanza del servizio di notifica appropriato
     *
     * @param string|null $type Il tipo di servizio richiesto
     * @return NotificationServiceInterface L'istanza del servizio
     */
    public static function create(?string $type = null): NotificationServiceInterface
    {
        // Se non viene specificato un tipo, usa la configurazione di default
        $type = $type ?? config('notifications.default_service', 'disabled');

        Log::info('Creazione servizio di notifica', [
            'requested_type' => $type,
            'available_services' => self::getAvailableServices()
        ]);

        return match($type) {
            'email' => self::createEmailService(),
            'sms' => self::createSmsService(),
            'disabled' => self::createNullService(),
            default => self::createNullService()
        };
    }

    /**
     * Crea un servizio di notifica basato sulla configurazione corrente
     *
     * @return NotificationServiceInterface
     */
    public static function createFromConfig(): NotificationServiceInterface
    {
        $type = config('notifications.default_service', 'disabled');
        return self::create($type);
    }

    /**
     * Crea il servizio email
     */
    private static function createEmailService(): NotificationServiceInterface
    {
        $service = new EmailNotificationService();
        
        Log::info('Servizio email creato', [
            'available' => $service->isAvailable(),
            'debug_info' => $service->getDebugInfo()
        ]);

        return $service;
    }

    /**
     * Crea il servizio SMS
     */
    private static function createSmsService(): NotificationServiceInterface
    {
        $service = new SmsNotificationService();
        
        Log::info('Servizio SMS creato', [
            'available' => $service->isAvailable(),
            'debug_info' => $service->getDebugInfo()
        ]);

        return $service;
    }

    /**
     * Crea il servizio null object
     */
    private static function createNullService(): NotificationServiceInterface
    {
        $service = new NullNotificationService();
        
        Log::info('Servizio Null Object creato', [
            'reason' => 'Nessun servizio di notifica configurato o disponibile',
            'debug_info' => $service->getDebugInfo()
        ]);

        return $service;
    }

    /**
     * Ottiene la lista dei servizi disponibili
     *
     * @return array Lista dei tipi di servizio supportati
     */
    public static function getAvailableServices(): array
    {
        return ['email', 'sms', 'disabled'];
    }

    /**
     * Verifica se un tipo di servizio è supportato
     *
     * @param string $type Il tipo di servizio da verificare
     * @return bool True se supportato, false altrimenti
     */
    public static function isServiceSupported(string $type): bool
    {
        return in_array($type, self::getAvailableServices());
    }

    /**
     * Ottiene informazioni sui servizi configurati
     *
     * @return array Informazioni sui servizi
     */
    public static function getServicesInfo(): array
    {
        $services = [];
        
        foreach (self::getAvailableServices() as $type) {
            $service = self::create($type);
            $services[$type] = [
                'type' => $service->getType(),
                'available' => $service->isAvailable(),
                'debug_info' => $service->getDebugInfo()
            ];
        }

        return $services;
    }
}
