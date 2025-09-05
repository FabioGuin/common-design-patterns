<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ThrottlingDecorator implements NotificationInterface
{
    private NotificationInterface $notification;
    private int $limit;
    private int $window;
    private string $throttleKey;

    public function __construct(NotificationInterface $notification, int $limit = 5, int $window = 60)
    {
        $this->notification = $notification;
        $this->limit = $limit;
        $this->window = $window;
        $this->throttleKey = 'notification_throttle_' . $this->notification->getType();
    }

    /**
     * Invia una notifica con throttling
     */
    public function send(string $message, array $data = []): array
    {
        if (!$this->isAllowed()) {
            return [
                'success' => false,
                'type' => $this->notification->getType(),
                'error' => 'Rate limit exceeded',
                'throttle_info' => [
                    'limit' => $this->limit,
                    'window' => $this->window,
                    'remaining' => $this->getRemainingAttempts(),
                    'reset_at' => $this->getResetTime(),
                ],
                'cost' => $this->getCost(),
                'timestamp' => now()->toISOString(),
            ];
        }

        $result = $this->notification->send($message, $data);

        // Incrementa il contatore solo se l'invio è riuscito
        if ($result['success']) {
            $this->incrementCounter();
        }

        return $result;
    }

    /**
     * Ottiene il tipo di notifica
     */
    public function getType(): string
    {
        return $this->notification->getType() . '_with_throttling';
    }

    /**
     * Ottiene il costo della notifica
     */
    public function getCost(): float
    {
        return $this->notification->getCost() + 0.01; // Costo aggiuntivo per throttling
    }

    /**
     * Verifica se la notifica è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->notification->isAvailable() && $this->isAllowed();
    }

    /**
     * Ottiene la descrizione della notifica
     */
    public function getDescription(): string
    {
        return $this->notification->getDescription() . " (with throttling: {$this->limit}/{$this->window}s)";
    }

    /**
     * Verifica se l'invio è consentito
     */
    private function isAllowed(): bool
    {
        $current = $this->getCurrentCount();
        return $current < $this->limit;
    }

    /**
     * Ottiene il numero corrente di invii
     */
    private function getCurrentCount(): int
    {
        return Cache::get($this->throttleKey, 0);
    }

    /**
     * Incrementa il contatore
     */
    private function incrementCounter(): void
    {
        $current = $this->getCurrentCount();
        Cache::put($this->throttleKey, $current + 1, $this->window);
    }

    /**
     * Ottiene i tentativi rimanenti
     */
    public function getRemainingAttempts(): int
    {
        return max(0, $this->limit - $this->getCurrentCount());
    }

    /**
     * Ottiene il tempo di reset
     */
    public function getResetTime(): string
    {
        $ttl = Cache::get($this->throttleKey . '_ttl', $this->window);
        return now()->addSeconds($ttl)->toISOString();
    }

    /**
     * Resetta il throttling
     */
    public function resetThrottling(): void
    {
        Cache::forget($this->throttleKey);
        Cache::forget($this->throttleKey . '_ttl');
    }

    /**
     * Ottiene le informazioni sul throttling
     */
    public function getThrottleInfo(): array
    {
        return [
            'limit' => $this->limit,
            'window' => $this->window,
            'current' => $this->getCurrentCount(),
            'remaining' => $this->getRemainingAttempts(),
            'reset_at' => $this->getResetTime(),
            'is_allowed' => $this->isAllowed(),
        ];
    }
}
