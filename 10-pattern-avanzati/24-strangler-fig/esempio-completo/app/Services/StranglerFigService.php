<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StranglerFigService
{
    private array $migrationConfig = [];
    private array $featureStatus = [];
    private string $legacyBaseUrl;
    private string $modernBaseUrl;

    public function __construct()
    {
        $this->legacyBaseUrl = config('strangler-fig.legacy_url', 'http://localhost:8000/legacy');
        $this->modernBaseUrl = config('strangler-fig.modern_url', 'http://localhost:8000/modern');
        $this->initializeMigrationConfig();
    }

    /**
     * Inizializza la configurazione della migrazione
     */
    private function initializeMigrationConfig(): void
    {
        $this->migrationConfig = [
            'users' => [
                'status' => 'legacy', // legacy, migrating, modern
                'percentage' => 0,
                'startDate' => null,
                'endDate' => null
            ],
            'products' => [
                'status' => 'legacy',
                'percentage' => 0,
                'startDate' => null,
                'endDate' => null
            ],
            'orders' => [
                'status' => 'legacy',
                'percentage' => 0,
                'startDate' => null,
                'endDate' => null
            ]
        ];

        // Carica configurazione da cache se disponibile
        $cachedConfig = Cache::get('strangler_fig_config');
        if ($cachedConfig) {
            $this->migrationConfig = array_merge($this->migrationConfig, $cachedConfig);
        }
    }

    /**
     * Route una richiesta al sistema appropriato
     */
    public function routeRequest(string $feature, array $requestData = []): array
    {
        $config = $this->migrationConfig[$feature] ?? null;
        
        if (!$config) {
            throw new \InvalidArgumentException("Feature '{$feature}' not configured");
        }

        $targetSystem = $this->determineTargetSystem($feature, $config, $requestData);
        
        Log::info("Routing request", [
            'feature' => $feature,
            'target_system' => $targetSystem,
            'config' => $config
        ]);

        return $this->executeRequest($targetSystem, $feature, $requestData);
    }

    /**
     * Determina il sistema target per una richiesta
     */
    private function determineTargetSystem(string $feature, array $config, array $requestData): string
    {
        switch ($config['status']) {
            case 'legacy':
                return 'legacy';
            
            case 'modern':
                return 'modern';
            
            case 'migrating':
                return $this->determineMigratingTarget($config, $requestData);
            
            default:
                return 'legacy';
        }
    }

    /**
     * Determina il target durante la migrazione
     */
    private function determineMigratingTarget(array $config, array $requestData): string
    {
        $percentage = $config['percentage'];
        
        // Usa l'ID utente per determinare il routing consistente
        $userId = $requestData['user_id'] ?? 0;
        $hash = crc32($userId . $config['startDate']);
        $userPercentage = abs($hash) % 100;
        
        if ($userPercentage < $percentage) {
            return 'modern';
        } else {
            return 'legacy';
        }
    }

    /**
     * Esegue la richiesta sul sistema target
     */
    private function executeRequest(string $targetSystem, string $feature, array $requestData): array
    {
        $baseUrl = $targetSystem === 'legacy' ? $this->legacyBaseUrl : $this->modernBaseUrl;
        $url = $baseUrl . '/' . $feature;
        
        try {
            // Simula la chiamata HTTP
            $response = $this->simulateHttpRequest($url, $requestData);
            
            return [
                'success' => true,
                'data' => $response,
                'target_system' => $targetSystem,
                'feature' => $feature,
                'timestamp' => now()->toISOString()
            ];
            
        } catch (\Exception $e) {
            Log::error("Request failed", [
                'target_system' => $targetSystem,
                'feature' => $feature,
                'error' => $e->getMessage()
            ]);
            
            // Fallback al sistema legacy se il moderno fallisce
            if ($targetSystem === 'modern') {
                return $this->executeRequest('legacy', $feature, $requestData);
            }
            
            throw $e;
        }
    }

    /**
     * Simula una richiesta HTTP
     */
    private function simulateHttpRequest(string $url, array $requestData): array
    {
        // Simula un tempo di risposta variabile
        usleep(mt_rand(10000, 50000)); // 10-50ms
        
        return [
            'url' => $url,
            'data' => $requestData,
            'response_time' => mt_rand(50, 200),
            'system' => str_contains($url, '/legacy/') ? 'legacy' : 'modern'
        ];
    }

    /**
     * Avvia la migrazione di una funzionalità
     */
    public function startMigration(string $feature, int $percentage = 0): bool
    {
        if (!isset($this->migrationConfig[$feature])) {
            return false;
        }

        $this->migrationConfig[$feature] = [
            'status' => 'migrating',
            'percentage' => $percentage,
            'startDate' => now()->toISOString(),
            'endDate' => null
        ];

        $this->saveConfig();
        
        Log::info("Migration started", [
            'feature' => $feature,
            'percentage' => $percentage
        ]);

        return true;
    }

    /**
     * Completa la migrazione di una funzionalità
     */
    public function completeMigration(string $feature): bool
    {
        if (!isset($this->migrationConfig[$feature])) {
            return false;
        }

        $this->migrationConfig[$feature] = [
            'status' => 'modern',
            'percentage' => 100,
            'startDate' => $this->migrationConfig[$feature]['startDate'],
            'endDate' => now()->toISOString()
        ];

        $this->saveConfig();
        
        Log::info("Migration completed", [
            'feature' => $feature
        ]);

        return true;
    }

    /**
     * Fa rollback di una funzionalità al sistema legacy
     */
    public function rollbackMigration(string $feature): bool
    {
        if (!isset($this->migrationConfig[$feature])) {
            return false;
        }

        $this->migrationConfig[$feature] = [
            'status' => 'legacy',
            'percentage' => 0,
            'startDate' => null,
            'endDate' => now()->toISOString()
        ];

        $this->saveConfig();
        
        Log::info("Migration rolled back", [
            'feature' => $feature
        ]);

        return true;
    }

    /**
     * Aggiorna la percentuale di migrazione
     */
    public function updateMigrationPercentage(string $feature, int $percentage): bool
    {
        if (!isset($this->migrationConfig[$feature]) || 
            $this->migrationConfig[$feature]['status'] !== 'migrating') {
            return false;
        }

        $this->migrationConfig[$feature]['percentage'] = min(100, max(0, $percentage));
        $this->saveConfig();

        return true;
    }

    /**
     * Ottiene lo stato della migrazione
     */
    public function getMigrationStatus(): array
    {
        return $this->migrationConfig;
    }

    /**
     * Ottiene lo stato di una funzionalità specifica
     */
    public function getFeatureStatus(string $feature): ?array
    {
        return $this->migrationConfig[$feature] ?? null;
    }

    /**
     * Ottiene le statistiche della migrazione
     */
    public function getMigrationStats(): array
    {
        $total = count($this->migrationConfig);
        $legacy = 0;
        $migrating = 0;
        $modern = 0;

        foreach ($this->migrationConfig as $config) {
            switch ($config['status']) {
                case 'legacy':
                    $legacy++;
                    break;
                case 'migrating':
                    $migrating++;
                    break;
                case 'modern':
                    $modern++;
                    break;
            }
        }

        return [
            'total_features' => $total,
            'legacy_features' => $legacy,
            'migrating_features' => $migrating,
            'modern_features' => $modern,
            'migration_progress' => $total > 0 ? (($migrating + $modern) / $total) * 100 : 0
        ];
    }

    /**
     * Salva la configurazione nella cache
     */
    private function saveConfig(): void
    {
        Cache::put('strangler_fig_config', $this->migrationConfig, 3600); // 1 ora
    }

    /**
     * Ottiene l'ID del pattern per identificazione
     */
    public function getId(): string
    {
        return 'strangler-fig-pattern-' . uniqid();
    }

    /**
     * Testa il routing per una funzionalità
     */
    public function testFeature(string $feature, int $numRequests = 10): array
    {
        $results = [];
        $legacyCount = 0;
        $modernCount = 0;

        for ($i = 0; $i < $numRequests; $i++) {
            $requestData = [
                'user_id' => $i,
                'request_id' => $i + 1,
                'timestamp' => now()->toISOString()
            ];

            $result = $this->routeRequest($feature, $requestData);
            $results[] = $result;

            if ($result['target_system'] === 'legacy') {
                $legacyCount++;
            } else {
                $modernCount++;
            }
        }

        return [
            'feature' => $feature,
            'total_requests' => $numRequests,
            'legacy_requests' => $legacyCount,
            'modern_requests' => $modernCount,
            'legacy_percentage' => ($legacyCount / $numRequests) * 100,
            'modern_percentage' => ($modernCount / $numRequests) * 100,
            'results' => $results
        ];
    }
}
