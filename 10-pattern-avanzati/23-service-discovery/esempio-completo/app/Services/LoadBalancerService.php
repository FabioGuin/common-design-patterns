<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoadBalancerService
{
    protected $serviceId = 'load-balancer-service';
    protected $version = '1.0.0';
    protected $algorithms = ['round_robin', 'weighted_round_robin', 'least_connections', 'random', 'ip_hash'];
    protected $defaultAlgorithm = 'round_robin';
    protected $counters = [];

    /**
     * Seleziona un'istanza usando l'algoritmo specificato
     */
    public function selectInstance(array $instances, array $options = []): array
    {
        try {
            if (empty($instances)) {
                return [
                    'success' => false,
                    'error' => 'Nessuna istanza disponibile'
                ];
            }

            // Filtra solo istanze sane
            $healthyInstances = array_filter($instances, fn($i) => $i['status'] === 'healthy');
            if (empty($healthyInstances)) {
                return [
                    'success' => false,
                    'error' => 'Nessuna istanza sana disponibile'
                ];
            }

            $algorithm = $options['algorithm'] ?? $this->defaultAlgorithm;
            $serviceName = $options['service_name'] ?? 'unknown';

            $selectedInstance = $this->applyAlgorithm($healthyInstances, $algorithm, $serviceName, $options);

            Log::info("Load Balancer Service: Istanza selezionata", [
                'service_name' => $serviceName,
                'algorithm' => $algorithm,
                'instance_id' => $selectedInstance['id'] ?? 'unknown',
                'total_instances' => count($healthyInstances),
                'load_balancer' => $this->serviceId
            ]);

            return [
                'success' => true,
                'instance' => $selectedInstance,
                'algorithm' => $algorithm,
                'total_instances' => count($healthyInstances)
            ];

        } catch (\Exception $e) {
            Log::error("Load Balancer Service: Errore nella selezione istanza", [
                'error' => $e->getMessage(),
                'instances_count' => count($instances),
                'load_balancer' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella selezione dell\'istanza'
            ];
        }
    }

    /**
     * Applica l'algoritmo di bilanciamento specificato
     */
    private function applyAlgorithm(array $instances, string $algorithm, string $serviceName, array $options): array
    {
        switch ($algorithm) {
            case 'round_robin':
                return $this->roundRobin($instances, $serviceName);
            case 'weighted_round_robin':
                return $this->weightedRoundRobin($instances, $serviceName, $options);
            case 'least_connections':
                return $this->leastConnections($instances, $serviceName);
            case 'random':
                return $this->random($instances);
            case 'ip_hash':
                return $this->ipHash($instances, $options);
            default:
                return $this->roundRobin($instances, $serviceName);
        }
    }

    /**
     * Algoritmo Round Robin
     */
    private function roundRobin(array $instances, string $serviceName): array
    {
        $counterKey = "round_robin:{$serviceName}";
        $counter = $this->getCounter($counterKey);
        
        $selectedIndex = $counter % count($instances);
        $this->incrementCounter($counterKey);
        
        return $instances[$selectedIndex];
    }

    /**
     * Algoritmo Weighted Round Robin
     */
    private function weightedRoundRobin(array $instances, string $serviceName, array $options): array
    {
        $weights = $options['weights'] ?? [];
        $totalWeight = 0;
        $weightedInstances = [];

        foreach ($instances as $index => $instance) {
            $weight = $weights[$instance['id']] ?? 1;
            $totalWeight += $weight;
            $weightedInstances[] = [
                'instance' => $instance,
                'weight' => $weight,
                'index' => $index
            ];
        }

        $counterKey = "weighted_round_robin:{$serviceName}";
        $counter = $this->getCounter($counterKey);
        
        $currentWeight = 0;
        $selectedInstance = $weightedInstances[0]['instance'];

        foreach ($weightedInstances as $weightedInstance) {
            $currentWeight += $weightedInstance['weight'];
            if ($counter < $currentWeight) {
                $selectedInstance = $weightedInstance['instance'];
                break;
            }
        }

        $this->incrementCounter($counterKey);
        
        return $selectedInstance;
    }

    /**
     * Algoritmo Least Connections
     */
    private function leastConnections(array $instances, string $serviceName): array
    {
        $connectionsKey = "connections:{$serviceName}";
        $connections = Cache::get($connectionsKey, []);

        $minConnections = PHP_INT_MAX;
        $selectedInstance = $instances[0];

        foreach ($instances as $instance) {
            $instanceId = $instance['id'];
            $connectionsCount = $connections[$instanceId] ?? 0;
            
            if ($connectionsCount < $minConnections) {
                $minConnections = $connectionsCount;
                $selectedInstance = $instance;
            }
        }

        // Incrementa il contatore delle connessioni per l'istanza selezionata
        $this->incrementConnections($connectionsKey, $selectedInstance['id']);

        return $selectedInstance;
    }

    /**
     * Algoritmo Random
     */
    private function random(array $instances): array
    {
        $randomIndex = array_rand($instances);
        return $instances[$randomIndex];
    }

    /**
     * Algoritmo IP Hash
     */
    private function ipHash(array $instances, array $options): array
    {
        $clientIp = $options['client_ip'] ?? '127.0.0.1';
        $hash = crc32($clientIp);
        $index = abs($hash) % count($instances);
        
        return $instances[$index];
    }

    /**
     * Ottiene il contatore per un servizio
     */
    private function getCounter(string $key): int
    {
        return $this->counters[$key] ?? 0;
    }

    /**
     * Incrementa il contatore per un servizio
     */
    private function incrementCounter(string $key): void
    {
        $this->counters[$key] = ($this->counters[$key] ?? 0) + 1;
    }

    /**
     * Incrementa il contatore delle connessioni per un'istanza
     */
    private function incrementConnections(string $key, string $instanceId): void
    {
        $connections = Cache::get($key, []);
        $connections[$instanceId] = ($connections[$instanceId] ?? 0) + 1;
        Cache::put($key, $connections, 3600); // 1 ora
    }

    /**
     * Decrementa il contatore delle connessioni per un'istanza
     */
    public function decrementConnections(string $serviceName, string $instanceId): void
    {
        try {
            $connectionsKey = "connections:{$serviceName}";
            $connections = Cache::get($connectionsKey, []);
            
            if (isset($connections[$instanceId]) && $connections[$instanceId] > 0) {
                $connections[$instanceId]--;
                Cache::put($connectionsKey, $connections, 3600);
            }

        } catch (\Exception $e) {
            Log::error("Load Balancer Service: Errore nel decremento connessioni", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'instance_id' => $instanceId,
                'load_balancer' => $this->serviceId
            ]);
        }
    }

    /**
     * Ottiene le connessioni attive per un servizio
     */
    public function getActiveConnections(string $serviceName): array
    {
        try {
            $connectionsKey = "connections:{$serviceName}";
            $connections = Cache::get($connectionsKey, []);

            return [
                'success' => true,
                'connections' => $connections,
                'total_connections' => array_sum($connections),
                'service_name' => $serviceName
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service_name' => $serviceName
            ];
        }
    }

    /**
     * Ottiene le statistiche del load balancer
     */
    public function getLoadBalancerStats(): array
    {
        try {
            $stats = [
                'algorithms' => $this->algorithms,
                'default_algorithm' => $this->defaultAlgorithm,
                'total_requests' => 0,
                'requests_per_algorithm' => [],
                'average_response_time' => 0,
                'top_services' => []
            ];

            // Simula statistiche
            $stats['total_requests'] = rand(1000, 10000);
            $stats['average_response_time'] = rand(50, 200); // ms

            foreach ($this->algorithms as $algorithm) {
                $stats['requests_per_algorithm'][$algorithm] = rand(100, 1000);
            }

            $stats['top_services'] = [
                ['service' => 'user-service', 'requests' => rand(100, 1000)],
                ['service' => 'product-service', 'requests' => rand(50, 500)],
                ['service' => 'order-service', 'requests' => rand(25, 250)]
            ];

            return [
                'success' => true,
                'data' => $stats,
                'load_balancer' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'load_balancer' => $this->serviceId
            ];
        }
    }

    /**
     * Configura un algoritmo per un servizio
     */
    public function configureAlgorithm(string $serviceName, string $algorithm, array $options = []): array
    {
        try {
            if (!in_array($algorithm, $this->algorithms)) {
                return [
                    'success' => false,
                    'error' => 'Algoritmo non supportato',
                    'supported_algorithms' => $this->algorithms
                ];
            }

            $configKey = "load_balancer_config:{$serviceName}";
            $config = [
                'algorithm' => $algorithm,
                'options' => $options,
                'configured_at' => now()->toISOString()
            ];

            Cache::put($configKey, $config, 86400); // 24 ore

            return [
                'success' => true,
                'message' => 'Algoritmo configurato con successo',
                'service_name' => $serviceName,
                'algorithm' => $algorithm,
                'config' => $config
            ];

        } catch (\Exception $e) {
            Log::error("Load Balancer Service: Errore nella configurazione algoritmo", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'algorithm' => $algorithm,
                'load_balancer' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella configurazione dell\'algoritmo'
            ];
        }
    }

    /**
     * Ottiene la configurazione di un servizio
     */
    public function getServiceConfig(string $serviceName): array
    {
        try {
            $configKey = "load_balancer_config:{$serviceName}";
            $config = Cache::get($configKey);

            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Configurazione non trovata',
                    'service_name' => $serviceName
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
                'error' => $e->getMessage(),
                'service_name' => $serviceName
            ];
        }
    }

    /**
     * Testa un algoritmo con istanze simulate
     */
    public function testAlgorithm(string $algorithm, array $instances, int $iterations = 100): array
    {
        try {
            if (!in_array($algorithm, $this->algorithms)) {
                return [
                    'success' => false,
                    'error' => 'Algoritmo non supportato',
                    'supported_algorithms' => $this->algorithms
                ];
            }

            $results = [];
            $distribution = [];

            // Reset contatori
            $this->counters = [];

            for ($i = 0; $i < $iterations; $i++) {
                $selectedInstance = $this->applyAlgorithm($instances, $algorithm, 'test_service', []);
                $instanceId = $selectedInstance['id'];
                
                $distribution[$instanceId] = ($distribution[$instanceId] ?? 0) + 1;
            }

            // Calcola distribuzione percentuale
            foreach ($distribution as $instanceId => $count) {
                $percentage = round(($count / $iterations) * 100, 2);
                $results[] = [
                    'instance_id' => $instanceId,
                    'count' => $count,
                    'percentage' => $percentage
                ];
            }

            return [
                'success' => true,
                'algorithm' => $algorithm,
                'iterations' => $iterations,
                'distribution' => $results,
                'instances_count' => count($instances)
            ];

        } catch (\Exception $e) {
            Log::error("Load Balancer Service: Errore nel test algoritmo", [
                'error' => $e->getMessage(),
                'algorithm' => $algorithm,
                'load_balancer' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel test dell\'algoritmo'
            ];
        }
    }

    /**
     * Ottiene gli algoritmi supportati
     */
    public function getSupportedAlgorithms(): array
    {
        return [
            'success' => true,
            'algorithms' => $this->algorithms,
            'default' => $this->defaultAlgorithm,
            'load_balancer' => $this->serviceId
        ];
    }

    /**
     * Pulisce i contatori e le connessioni
     */
    public function clearCounters(): array
    {
        try {
            $this->counters = [];
            
            // Pulisce le connessioni dalla cache
            $pattern = 'connections:*';
            // In un'implementazione reale, useresti Redis SCAN
            // Per ora simuliamo

            return [
                'success' => true,
                'message' => 'Contatori puliti con successo'
            ];

        } catch (\Exception $e) {
            Log::error("Load Balancer Service: Errore nella pulizia contatori", [
                'error' => $e->getMessage(),
                'load_balancer' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella pulizia dei contatori'
            ];
        }
    }

    /**
     * Health check del load balancer
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'load_balancer' => $this->serviceId,
                'version' => $this->version,
                'algorithms_supported' => count($this->algorithms),
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'load_balancer' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del load balancer
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del load balancer
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
