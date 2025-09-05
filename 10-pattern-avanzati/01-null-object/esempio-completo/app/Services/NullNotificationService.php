<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Implementazione Null Object per i servizi di notifica
 * 
 * Questa classe implementa il Null Object Pattern, fornendo
 * un comportamento neutro quando i servizi di notifica non sono
 * disponibili o configurati. Evita la necessità di controlli null
 * multipli nel codice.
 */
class NullNotificationService implements NotificationServiceInterface
{
    /**
     * Invia una notifica (comportamento neutro)
     * 
     * Non invia effettivamente alcuna notifica, ma restituisce
     * un valore che indica "nessuna operazione eseguita".
     */
    public function send(string $message, string $recipient): bool
    {
        // Logga l'uso del null object per debugging
        Log::info('Null Object: Tentativo di invio notifica ignorato', [
            'recipient' => $recipient,
            'message' => $message,
            'service' => 'null',
            'reason' => 'Servizio di notifica non configurato o non disponibile'
        ]);

        // Restituisce false per indicare che nessuna notifica è stata inviata
        // ma non rappresenta un errore
        return false;
    }

    /**
     * Verifica se il servizio è disponibile
     * 
     * Restituisce sempre false per indicare che questo
     * non è un servizio reale.
     */
    public function isAvailable(): bool
    {
        return false;
    }

    /**
     * Restituisce il tipo di servizio
     * 
     * Identifica chiaramente questo come un null object.
     */
    public function getType(): string
    {
        return 'null';
    }

    /**
     * Ottiene informazioni di debug
     * 
     * Fornisce informazioni utili per il debugging,
     * indicando che si sta usando un null object.
     */
    public function getDebugInfo(): array
    {
        return [
            'type' => 'null',
            'available' => false,
            'description' => 'Null Object - Nessuna notifica viene inviata',
            'purpose' => 'Fornisce un comportamento neutro quando i servizi di notifica non sono disponibili',
            'usage' => 'Utilizzato automaticamente quando i servizi reali non sono configurati'
        ];
    }

    /**
     * Metodo aggiuntivo per verificare se si tratta di un null object
     * 
     * Utile per controlli espliciti quando necessario.
     */
    public function isNullObject(): bool
    {
        return true;
    }

    /**
     * Metodo per ottenere un messaggio descrittivo
     * 
     * Utile per logging o debugging avanzato.
     */
    public function getDescription(): string
    {
        return 'Null Object Pattern - Servizio di notifica non configurato';
    }
}
