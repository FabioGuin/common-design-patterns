<?php

namespace App\Services;

class BaseNotification implements NotificationInterface
{
    private string $type;
    private float $cost;
    private string $description;

    public function __construct(string $type = 'base', float $cost = 0.0, string $description = 'Base notification')
    {
        $this->type = $type;
        $this->cost = $cost;
        $this->description = $description;
    }

    /**
     * Invia una notifica base
     */
    public function send(string $message, array $data = []): array
    {
        try {
            // Simula l'invio della notifica
            $this->simulateSend($message, $data);

            return [
                'success' => true,
                'type' => $this->type,
                'message' => $message,
                'cost' => $this->cost,
                'timestamp' => now()->toISOString(),
                'data' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'cost' => $this->cost,
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Ottiene il tipo di notifica
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Ottiene il costo della notifica
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * Verifica se la notifica è disponibile
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Ottiene la descrizione della notifica
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Simula l'invio di una notifica
     */
    private function simulateSend(string $message, array $data): void
    {
        // In un'implementazione reale, qui invieresti effettivamente la notifica
        \Log::info('Notification sent', [
            'type' => $this->type,
            'message' => $message,
            'data' => $data,
            'cost' => $this->cost,
        ]);

        // Simula un piccolo delay
        usleep(100000); // 0.1 secondi
    }
}
