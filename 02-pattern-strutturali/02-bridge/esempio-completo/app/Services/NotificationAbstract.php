<?php

namespace App\Services;

abstract class NotificationAbstract
{
    protected MessageFormatterInterface $formatter;

    public function __construct(MessageFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Invia una notifica
     *
     * @param string $message Messaggio da inviare
     * @param array $data Dati aggiuntivi
     * @return array Risultato dell'invio
     */
    abstract public function send(string $message, array $data = []): array;

    /**
     * Ottiene il canale di notifica
     *
     * @return string Nome del canale
     */
    abstract public function getChannel(): string;

    /**
     * Verifica se il canale è disponibile
     *
     * @return bool True se disponibile
     */
    abstract public function isAvailable(): bool;

    /**
     * Ottiene il formattatore corrente
     *
     * @return MessageFormatterInterface
     */
    public function getFormatter(): MessageFormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Cambia il formattatore
     *
     * @param MessageFormatterInterface $formatter Nuovo formattatore
     */
    public function setFormatter(MessageFormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * Formatta il messaggio usando il formattatore corrente
     *
     * @param string $message Messaggio da formattare
     * @param array $data Dati per la formattazione
     * @return string Messaggio formattato
     */
    protected function formatMessage(string $message, array $data = []): string
    {
        return $this->formatter->format($message, $data);
    }

    /**
     * Logga l'invio della notifica
     *
     * @param string $message Messaggio inviato
     * @param array $data Dati della notifica
     * @param bool $success Se l'invio è riuscito
     */
    protected function logNotification(string $message, array $data, bool $success): void
    {
        $logData = [
            'channel' => $this->getChannel(),
            'formatter' => $this->formatter->getType(),
            'message' => $message,
            'data' => $data,
            'success' => $success,
            'timestamp' => now()->toISOString(),
        ];

        if ($success) {
            \Log::info('Notification sent successfully', $logData);
        } else {
            \Log::error('Notification failed to send', $logData);
        }
    }
}
