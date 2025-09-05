<?php

namespace App\Contracts;

/**
 * Interfaccia per i servizi di notifica
 * 
 * Definisce il contratto che tutti i servizi di notifica devono implementare,
 * inclusi i null object per gestire scenari di fallback.
 */
interface NotificationServiceInterface
{
    /**
     * Invia una notifica al destinatario specificato
     *
     * @param string $message Il messaggio da inviare
     * @param string $recipient Il destinatario della notifica
     * @return bool True se l'invio è riuscito, false altrimenti
     */
    public function send(string $message, string $recipient): bool;

    /**
     * Verifica se il servizio è disponibile
     *
     * @return bool True se il servizio è disponibile, false altrimenti
     */
    public function isAvailable(): bool;

    /**
     * Ottiene il tipo di servizio
     *
     * @return string Il tipo di servizio (email, sms, null, etc.)
     */
    public function getType(): string;

    /**
     * Ottiene informazioni di debug sul servizio
     *
     * @return array Array con informazioni di debug
     */
    public function getDebugInfo(): array;
}
