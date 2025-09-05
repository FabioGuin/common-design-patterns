<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthCheckerService
{
    private array $healthCache = [];
    private int $cacheTimeout = 30; // secondi
    private int $timeout = 5; // secondi per la richiesta

    /**
     * Verifica se un server è sano
     */
    public function isHealthy(string $url): bool
    {
        $cacheKey = $this->getCacheKey($url);
        
        // Controlla la cache
        if (isset($this->healthCache[$cacheKey])) {
            $cached = $this->healthCache[$cacheKey];
            if (time() - $cached['timestamp'] < $this->cacheTimeout) {
                return $cached['is_healthy'];
            }
        }

        // Esegue il controllo di salute
        $isHealthy = $this->performHealthCheck($url);
        
        // Aggiorna la cache
        $this->healthCache[$cacheKey] = [
            'is_healthy' => $isHealthy,
            'timestamp' => time()
        ];

        return $isHealthy;
    }

    /**
     * Esegue il controllo di salute effettivo
     */
    private function performHealthCheck(string $url): bool
    {
        try {
            $healthUrl = rtrim($url, '/') . '/health';
            
            $response = Http::timeout($this->timeout)
                ->get($healthUrl);

            $isHealthy = $response->successful() && 
                        $response->json('status') === 'healthy';

            Log::debug("Health check eseguito", [
                'url' => $healthUrl,
                'status_code' => $response->status(),
                'is_healthy' => $isHealthy
            ]);

            return $isHealthy;

        } catch (\Exception $e) {
            Log::warning("Health check fallito", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Controlla la salute di tutti i server
     */
    public function checkAllServers(array $servers): array
    {
        $results = [];
        
        foreach ($servers as $server) {
            $results[$server['id']] = [
                'url' => $server['url'],
                'is_healthy' => $this->isHealthy($server['url']),
                'last_check' => now()->toISOString()
            ];
        }

        return $results;
    }

    /**
     * Ottiene le statistiche di salute
     */
    public function getHealthStats(): array
    {
        $total = count($this->healthCache);
        $healthy = array_filter($this->healthCache, fn($item) => $item['is_healthy']);
        
        return [
            'total_servers' => $total,
            'healthy_servers' => count($healthy),
            'unhealthy_servers' => $total - count($healthy),
            'health_rate' => $total > 0 ? (count($healthy) / $total) * 100 : 0
        ];
    }

    /**
     * Pulisce la cache di salute
     */
    public function clearHealthCache(): void
    {
        $this->healthCache = [];
        Log::info("Cache di salute pulita");
    }

    /**
     * Imposta il timeout per i controlli di salute
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Imposta il timeout della cache
     */
    public function setCacheTimeout(int $timeout): void
    {
        $this->cacheTimeout = $timeout;
    }

    /**
     * Ottiene la chiave di cache per un URL
     */
    private function getCacheKey(string $url): string
    {
        return 'health_' . md5($url);
    }

    /**
     * Simula un controllo di salute per test
     */
    public function simulateHealthCheck(string $url, bool $isHealthy = true): void
    {
        $cacheKey = $this->getCacheKey($url);
        $this->healthCache[$cacheKey] = [
            'is_healthy' => $isHealthy,
            'timestamp' => time()
        ];
    }
}
