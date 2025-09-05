<?php

namespace App\Services;

use App\Services\HealthCheckerService;
use Illuminate\Support\Facades\Log;

class LoadBalancerService
{
    private array $servers = [];
    private string $algorithm = 'round_robin';
    private int $currentIndex = 0;
    private HealthCheckerService $healthChecker;
    private array $serverStats = [];

    public function __construct(HealthCheckerService $healthChecker)
    {
        $this->healthChecker = $healthChecker;
        $this->initializeDefaultServers();
    }

    /**
     * Aggiunge un server al load balancer
     */
    public function addServer(string $id, string $url, int $weight = 1): void
    {
        $server = [
            'id' => $id,
            'url' => $url,
            'weight' => $weight,
            'active_connections' => 0,
            'total_requests' => 0,
            'last_used' => null,
            'is_healthy' => true
        ];

        $this->servers[$id] = $server;
        $this->serverStats[$id] = [
            'requests' => 0,
            'errors' => 0,
            'avg_response_time' => 0
        ];

        Log::info("Server aggiunto al load balancer", ['server_id' => $id, 'url' => $url]);
    }

    /**
     * Rimuove un server dal load balancer
     */
    public function removeServer(string $id): bool
    {
        if (isset($this->servers[$id])) {
            unset($this->servers[$id]);
            unset($this->serverStats[$id]);
            Log::info("Server rimosso dal load balancer", ['server_id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Imposta l'algoritmo di distribuzione
     */
    public function setAlgorithm(string $algorithm): void
    {
        $allowedAlgorithms = ['round_robin', 'least_connections', 'weighted', 'ip_hash'];
        
        if (in_array($algorithm, $allowedAlgorithms)) {
            $this->algorithm = $algorithm;
            Log::info("Algoritmo di distribuzione cambiato", ['algorithm' => $algorithm]);
        }
    }

    /**
     * Seleziona un server per la richiesta
     */
    public function selectServer(array $requestData = []): ?array
    {
        $availableServers = $this->getHealthyServers();
        
        if (empty($availableServers)) {
            Log::warning("Nessun server disponibile per la richiesta");
            return null;
        }

        $selectedServer = $this->applyAlgorithm($availableServers, $requestData);
        
        if ($selectedServer) {
            $this->updateServerStats($selectedServer['id']);
            Log::debug("Server selezionato", [
                'server_id' => $selectedServer['id'],
                'algorithm' => $this->algorithm
            ]);
        }

        return $selectedServer;
    }

    /**
     * Ottiene tutti i server
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * Ottiene le statistiche dei server
     */
    public function getServerStats(): array
    {
        return $this->serverStats;
    }

    /**
     * Ottiene l'algoritmo corrente
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'load-balancer-pattern-' . uniqid();
    }

    /**
     * Inizializza i server di default
     */
    private function initializeDefaultServers(): void
    {
        $this->addServer('server-1', 'http://localhost:8001', 1);
        $this->addServer('server-2', 'http://localhost:8002', 1);
        $this->addServer('server-3', 'http://localhost:8003', 2);
    }

    /**
     * Ottiene solo i server sani
     */
    private function getHealthyServers(): array
    {
        return array_filter($this->servers, function ($server) {
            return $server['is_healthy'] && $this->healthChecker->isHealthy($server['url']);
        });
    }

    /**
     * Applica l'algoritmo di selezione
     */
    private function applyAlgorithm(array $servers, array $requestData): ?array
    {
        switch ($this->algorithm) {
            case 'round_robin':
                return $this->roundRobinSelection($servers);
            case 'least_connections':
                return $this->leastConnectionsSelection($servers);
            case 'weighted':
                return $this->weightedSelection($servers);
            case 'ip_hash':
                return $this->ipHashSelection($servers, $requestData);
            default:
                return $this->roundRobinSelection($servers);
        }
    }

    /**
     * Selezione Round Robin
     */
    private function roundRobinSelection(array $servers): array
    {
        $serverList = array_values($servers);
        $selected = $serverList[$this->currentIndex % count($serverList)];
        $this->currentIndex++;
        return $selected;
    }

    /**
     * Selezione Least Connections
     */
    private function leastConnectionsSelection(array $servers): array
    {
        $minConnections = min(array_column($servers, 'active_connections'));
        $candidates = array_filter($servers, function ($server) use ($minConnections) {
            return $server['active_connections'] === $minConnections;
        });
        
        return array_values($candidates)[0] ?? null;
    }

    /**
     * Selezione Weighted
     */
    private function weightedSelection(array $servers): array
    {
        $totalWeight = array_sum(array_column($servers, 'weight'));
        $random = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($servers as $server) {
            $currentWeight += $server['weight'];
            if ($random <= $currentWeight) {
                return $server;
            }
        }
        
        return array_values($servers)[0];
    }

    /**
     * Selezione IP Hash
     */
    private function ipHashSelection(array $servers, array $requestData): array
    {
        $ip = $requestData['ip'] ?? '127.0.0.1';
        $hash = crc32($ip);
        $index = abs($hash) % count($servers);
        return array_values($servers)[$index];
    }

    /**
     * Aggiorna le statistiche del server
     */
    private function updateServerStats(string $serverId): void
    {
        if (isset($this->servers[$serverId])) {
            $this->servers[$serverId]['active_connections']++;
            $this->servers[$serverId]['total_requests']++;
            $this->servers[$serverId]['last_used'] = now();
        }

        if (isset($this->serverStats[$serverId])) {
            $this->serverStats[$serverId]['requests']++;
        }
    }

    /**
     * Simula una richiesta al server selezionato
     */
    public function routeRequest(array $requestData = []): array
    {
        $server = $this->selectServer($requestData);
        
        if (!$server) {
            return [
                'success' => false,
                'error' => 'Nessun server disponibile',
                'server' => null
            ];
        }

        // Simula la richiesta al server
        $startTime = microtime(true);
        
        try {
            // In un'implementazione reale, qui faremmo la richiesta HTTP
            $response = $this->simulateServerResponse($server);
            
            $responseTime = (microtime(true) - $startTime) * 1000; // in millisecondi
            
            $this->updateResponseTime($server['id'], $responseTime);
            
            return [
                'success' => true,
                'response' => $response,
                'server' => $server,
                'response_time' => $responseTime
            ];
            
        } catch (\Exception $e) {
            $this->recordError($server['id']);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'server' => $server
            ];
        }
    }

    /**
     * Simula la risposta del server
     */
    private function simulateServerResponse(array $server): array
    {
        // Simula un tempo di risposta variabile
        usleep(mt_rand(10000, 50000)); // 10-50ms
        
        return [
            'message' => 'Risposta dal server ' . $server['id'],
            'timestamp' => now()->toISOString(),
            'server_url' => $server['url']
        ];
    }

    /**
     * Aggiorna il tempo di risposta medio
     */
    private function updateResponseTime(string $serverId, float $responseTime): void
    {
        if (isset($this->serverStats[$serverId])) {
            $stats = &$this->serverStats[$serverId];
            $stats['avg_response_time'] = ($stats['avg_response_time'] + $responseTime) / 2;
        }
    }

    /**
     * Registra un errore per il server
     */
    private function recordError(string $serverId): void
    {
        if (isset($this->serverStats[$serverId])) {
            $this->serverStats[$serverId]['errors']++;
        }
    }
}
