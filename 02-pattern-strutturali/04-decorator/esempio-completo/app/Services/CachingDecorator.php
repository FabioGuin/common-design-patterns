<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CachingDecorator implements NotificationInterface
{
    private NotificationInterface $notification;
    private int $ttl;
    private string $cachePrefix;

    public function __construct(NotificationInterface $notification, int $ttl = 300, string $cachePrefix = 'notification_')
    {
        $this->notification = $notification;
        $this->ttl = $ttl;
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * Invia una notifica con caching
     */
    public function send(string $message, array $data = []): array
    {
        $cacheKey = $this->generateCacheKey($message, $data);

        // Controlla se la notifica è già in cache
        if (Cache::has($cacheKey)) {
            $cachedResult = Cache::get($cacheKey);
            $cachedResult['from_cache'] = true;
            $cachedResult['cache_key'] = $cacheKey;
            
            \Log::info('Notification served from cache', [
                'cache_key' => $cacheKey,
                'type' => $this->notification->getType(),
            ]);
            
            return $cachedResult;
        }

        // Invia la notifica e metti in cache il risultato
        $result = $this->notification->send($message, $data);
        
        if ($result['success']) {
            Cache::put($cacheKey, $result, $this->ttl);
            
            \Log::info('Notification cached', [
                'cache_key' => $cacheKey,
                'ttl' => $this->ttl,
                'type' => $this->notification->getType(),
            ]);
        }

        return $result;
    }

    /**
     * Ottiene il tipo di notifica
     */
    public function getType(): string
    {
        return $this->notification->getType() . '_with_caching';
    }

    /**
     * Ottiene il costo della notifica
     */
    public function getCost(): float
    {
        return $this->notification->getCost() + 0.02; // Costo aggiuntivo per caching
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
        return $this->notification->getDescription() . " (with caching, TTL: {$this->ttl}s)";
    }

    /**
     * Genera una chiave di cache unica
     */
    private function generateCacheKey(string $message, array $data): string
    {
        $keyData = [
            'message' => $message,
            'data' => $data,
            'type' => $this->notification->getType(),
        ];

        return $this->cachePrefix . md5(serialize($keyData));
    }

    /**
     * Pulisce la cache per una notifica specifica
     */
    public function clearCache(string $message, array $data = []): bool
    {
        $cacheKey = $this->generateCacheKey($message, $data);
        return Cache::forget($cacheKey);
    }

    /**
     * Pulisce tutta la cache delle notifiche
     */
    public function clearAllCache(): bool
    {
        // In un'implementazione reale, useresti un tag o un pattern per pulire solo le notifiche
        return Cache::flush();
    }
}
