<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceRegistry;

class HealthCheckService
{
    protected $serviceId = 'health-check-service';
    protected $version = '1.0.0';
    protected $registry;
    protected $healthChecks = [];

    public function __construct(ServiceRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Esegue health check di un servizio
     */
    public function checkServiceHealth(string $serviceName, array $options = []): array
    {
        try {
            $timeout = $options['timeout'] ?? 30;
            $retries = $options['retries'] ?? 3;
            $interval = $options['interval'] ?? 5;

            // Ottieni le istanze del servizio
            $instancesResult = $this->registry->getServiceInstances($serviceName);
            if (!$instancesResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato',
                    'service_name' => $serviceName
                ];
            }

            $instances = $instancesResult['instances'];
            $healthResults = [];

            foreach ($instances as $instance) {
                $healthResult = $this->checkInstanceHealth($instance, $timeout, $retries, $interval);
                $healthResults[] = $healthResult;
                
                // Aggiorna lo status nel registry
                $this->updateInstanceStatus($instance['id'], $healthResult['status']);
            }

            $healthyCount = count(array_filter($healthResults, fn($r) => $r['status'] === 'healthy'));
            $totalCount = count($healthResults);

            return [
                'success' => true,
                'service_name' => $serviceName,
                'instances' => $healthResults,
                'summary' => [
                    'total' => $totalCount,
                    'healthy' => $healthyCount,
                    'unhealthy' => $totalCount - $healthyCount,
                    'health_percentage' => $totalCount > 0 ? round(($healthyCount / $totalCount) * 100, 2) : 0
                ],
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nel health check servizio", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'health_check' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel health check del servizio'
            ];
        }
    }

    /**
     * Esegue health check di un'istanza specifica
     */
    private function checkInstanceHealth(array $instance, int $timeout, int $retries, int $interval): array
    {
        try {
            $instanceId = $instance['id'];
            $address = $instance['address'];
            $port = $instance['port'];
            $serviceName = $instance['service_id'];

            $startTime = microtime(true);
            $lastError = null;

            for ($attempt = 1; $attempt <= $retries; $attempt++) {
                try {
                    $healthResult = $this->performHealthCheck($address, $port, $timeout);
                    
                    if ($healthResult['success']) {
                        $responseTime = microtime(true) - $startTime;
                        
                        return [
                            'instance_id' => $instanceId,
                            'address' => $address,
                            'port' => $port,
                            'status' => 'healthy',
                            'response_time' => round($responseTime * 1000, 2), // ms
                            'attempts' => $attempt,
                            'last_check' => now()->toISOString(),
                            'details' => $healthResult
                        ];
                    }
                    
                    $lastError = $healthResult['error'] ?? 'Unknown error';
                    
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }

                // Attendi prima del prossimo tentativo
                if ($attempt < $retries) {
                    sleep($interval);
                }
            }

            // Tutti i tentativi sono falliti
            $responseTime = microtime(true) - $startTime;
            
            return [
                'instance_id' => $instanceId,
                'address' => $address,
                'port' => $port,
                'status' => 'unhealthy',
                'response_time' => round($responseTime * 1000, 2), // ms
                'attempts' => $retries,
                'last_check' => now()->toISOString(),
                'error' => $lastError
            ];

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nel health check istanza", [
                'error' => $e->getMessage(),
                'instance_id' => $instance['id'] ?? 'unknown',
                'health_check' => $this->serviceId
            ]);

            return [
                'instance_id' => $instance['id'] ?? 'unknown',
                'address' => $instance['address'] ?? 'unknown',
                'port' => $instance['port'] ?? 'unknown',
                'status' => 'error',
                'response_time' => 0,
                'attempts' => 0,
                'last_check' => now()->toISOString(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Esegue il controllo di salute effettivo
     */
    private function performHealthCheck(string $address, int $port, int $timeout): array
    {
        try {
            // Simula health check HTTP
            $url = "http://{$address}:{$port}/health";
            
            // In un'implementazione reale, useresti Guzzle o cURL
            // Per ora simuliamo il risultato
            $simulatedResult = $this->simulateHealthCheck($address, $port);
            
            return $simulatedResult;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Simula un health check
     */
    private function simulateHealthCheck(string $address, int $port): array
    {
        // Simula latenza di rete
        usleep(rand(50000, 200000)); // 50-200ms
        
        // Simula probabilità di successo (90%)
        $success = rand(1, 100) <= 90;
        
        if ($success) {
            return [
                'success' => true,
                'status' => 'healthy',
                'response_time' => rand(10, 100), // ms
                'details' => [
                    'database' => 'connected',
                    'memory' => 'ok',
                    'disk' => 'ok',
                    'cpu' => 'ok'
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Service unavailable',
                'status' => 'unhealthy'
            ];
        }
    }

    /**
     * Aggiorna lo status di un'istanza nel registry
     */
    private function updateInstanceStatus(string $instanceId, string $status): void
    {
        try {
            // In un'implementazione reale, aggiorneresti il registry
            // Per ora simuliamo
            Log::info("Health Check Service: Status aggiornato", [
                'instance_id' => $instanceId,
                'status' => $status,
                'health_check' => $this->serviceId
            ]);

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nell'aggiornamento status", [
                'error' => $e->getMessage(),
                'instance_id' => $instanceId,
                'health_check' => $this->serviceId
            ]);
        }
    }

    /**
     * Esegue health check di tutti i servizi
     */
    public function checkAllServicesHealth(array $options = []): array
    {
        try {
            $allServicesResult = $this->registry->getAllServices();
            if (!$allServicesResult['success']) {
                return $allServicesResult;
            }

            $services = $allServicesResult['services'];
            $overallResults = [];

            foreach ($services as $service) {
                $serviceName = $service['name'];
                $healthResult = $this->checkServiceHealth($serviceName, $options);
                $overallResults[] = $healthResult;
            }

            $totalServices = count($overallResults);
            $healthyServices = count(array_filter($overallResults, fn($r) => $r['success'] && $r['summary']['health_percentage'] > 0));

            return [
                'success' => true,
                'services' => $overallResults,
                'overall_summary' => [
                    'total_services' => $totalServices,
                    'healthy_services' => $healthyServices,
                    'unhealthy_services' => $totalServices - $healthyServices,
                    'overall_health_percentage' => $totalServices > 0 ? round(($healthyServices / $totalServices) * 100, 2) : 0
                ],
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nel health check tutti i servizi", [
                'error' => $e->getMessage(),
                'health_check' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel health check di tutti i servizi'
            ];
        }
    }

    /**
     * Esegue health check periodico
     */
    public function performPeriodicHealthCheck(): array
    {
        try {
            $results = $this->checkAllServicesHealth();
            
            // Cache i risultati per 5 minuti
            Cache::put('health_check:all_services', $results, 300);
            
            Log::info("Health Check Service: Health check periodico completato", [
                'services_checked' => count($results['services'] ?? []),
                'overall_health' => $results['overall_summary']['overall_health_percentage'] ?? 0,
                'health_check' => $this->serviceId
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nel health check periodico", [
                'error' => $e->getMessage(),
                'health_check' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel health check periodico'
            ];
        }
    }

    /**
     * Ottiene i risultati dell'ultimo health check
     */
    public function getLastHealthCheckResults(): array
    {
        try {
            $cached = Cache::get('health_check:all_services');
            if ($cached) {
                return [
                    'success' => true,
                    'results' => $cached,
                    'cached' => true,
                    'timestamp' => now()->toISOString()
                ];
            }

            return [
                'success' => false,
                'error' => 'Nessun health check disponibile',
                'cached' => false
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'cached' => false
            ];
        }
    }

    /**
     * Ottiene le statistiche di health check
     */
    public function getHealthCheckStats(): array
    {
        try {
            $stats = [
                'total_checks' => 0,
                'successful_checks' => 0,
                'failed_checks' => 0,
                'average_response_time' => 0,
                'top_unhealthy_services' => [],
                'health_trend' => []
            ];

            // Simula statistiche
            $stats['total_checks'] = rand(1000, 10000);
            $stats['successful_checks'] = rand(800, $stats['total_checks']);
            $stats['failed_checks'] = $stats['total_checks'] - $stats['successful_checks'];
            $stats['average_response_time'] = rand(50, 200); // ms

            $stats['top_unhealthy_services'] = [
                ['service' => 'service-a', 'failures' => rand(10, 100)],
                ['service' => 'service-b', 'failures' => rand(5, 50)],
                ['service' => 'service-c', 'failures' => rand(1, 25)]
            ];

            // Simula trend degli ultimi 24 ore
            for ($i = 0; $i < 24; $i++) {
                $stats['health_trend'][] = [
                    'hour' => $i,
                    'health_percentage' => rand(80, 100)
                ];
            }

            return [
                'success' => true,
                'data' => $stats,
                'health_check' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'health_check' => $this->serviceId
            ];
        }
    }

    /**
     * Configura un health check personalizzato
     */
    public function configureHealthCheck(string $serviceName, array $config): array
    {
        try {
            $configKey = "health_check_config:{$serviceName}";
            Cache::put($configKey, $config, 86400); // 24 ore

            return [
                'success' => true,
                'message' => 'Health check configurato con successo',
                'service_name' => $serviceName,
                'config' => $config
            ];

        } catch (\Exception $e) {
            Log::error("Health Check Service: Errore nella configurazione health check", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'health_check' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella configurazione del health check'
            ];
        }
    }

    /**
     * Ottiene la configurazione di un health check
     */
    public function getHealthCheckConfig(string $serviceName): array
    {
        try {
            $configKey = "health_check_config:{$serviceName}";
            $config = Cache::get($configKey);

            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Configurazione non trovata'
                ];
            }

            return [
                'success' => true,
                'config' => $config,
                'service_name' => $serviceName
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Health check del servizio stesso
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'health_check' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'health_check' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
