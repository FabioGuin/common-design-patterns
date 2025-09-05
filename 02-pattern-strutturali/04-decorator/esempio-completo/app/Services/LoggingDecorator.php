<?php

namespace App\Services;

class LoggingDecorator implements NotificationInterface
{
    private NotificationInterface $notification;
    private string $logLevel;

    public function __construct(NotificationInterface $notification, string $logLevel = 'info')
    {
        $this->notification = $notification;
        $this->logLevel = $logLevel;
    }

    /**
     * Invia una notifica con logging
     */
    public function send(string $message, array $data = []): array
    {
        $this->logBeforeSend($message, $data);

        $result = $this->notification->send($message, $data);

        $this->logAfterSend($result);

        return $result;
    }

    /**
     * Ottiene il tipo di notifica
     */
    public function getType(): string
    {
        return $this->notification->getType() . '_with_logging';
    }

    /**
     * Ottiene il costo della notifica
     */
    public function getCost(): float
    {
        return $this->notification->getCost() + 0.01; // Costo aggiuntivo per logging
    }

    /**
     * Verifica se la notifica è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->notification->isAvailable();
    }

    /**
     * Ottiene la descrizione della notifica
     */
    public function getDescription(): string
    {
        return $this->notification->getDescription() . ' (with logging)';
    }

    /**
     * Logga prima dell'invio
     */
    private function logBeforeSend(string $message, array $data): void
    {
        $logData = [
            'action' => 'notification_send_start',
            'type' => $this->notification->getType(),
            'message' => $message,
            'data' => $data,
            'cost' => $this->getCost(),
            'timestamp' => now()->toISOString(),
        ];

        $this->writeLog($logData);
    }

    /**
     * Logga dopo l'invio
     */
    private function logAfterSend(array $result): void
    {
        $logData = [
            'action' => 'notification_send_end',
            'type' => $this->notification->getType(),
            'success' => $result['success'],
            'cost' => $this->getCost(),
            'timestamp' => now()->toISOString(),
        ];

        if (!$result['success']) {
            $logData['error'] = $result['error'] ?? 'Unknown error';
        }

        $this->writeLog($logData);
    }

    /**
     * Scrive il log
     */
    private function writeLog(array $data): void
    {
        $message = "Notification Log: {$data['action']}";
        
        switch ($this->logLevel) {
            case 'debug':
                \Log::debug($message, $data);
                break;
            case 'info':
                \Log::info($message, $data);
                break;
            case 'warning':
                \Log::warning($message, $data);
                break;
            case 'error':
                \Log::error($message, $data);
                break;
            default:
                \Log::info($message, $data);
        }
    }
}
