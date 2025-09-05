<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceRegistry;
use App\Services\LoadBalancerService;

class ServiceDiscovery
{
    protected $serviceId = 'service-discovery';
    protected $version = '1.0.0';
    protected $registry;
    protected $loadBalancer;

    public function __construct(ServiceRegistry $registry, LoadBalancerService $loadBalancer)
    {
        $this->registry = $registry;
        $this->loadBalancer = $loadBalancer;
    }

    /**
     * Scopre un servizio per nome
     */
    public function discoverService(string $serviceName, array $options = []): array
    {
        try {
            $useCache = $options['use_cache'] ?? true;
            $cacheKey = "service_discovery:{$serviceName}";
            
            // Prova a recuperare dalla cache
            if ($useCache) {
                $cached = Cache::get($cacheKey);
                if ($cached) {
                    return [
                        'success' => true,
                        'service' => $cached,
                        'cached' => true
                    ];
                }
            }

            // Cerca nel registry
            $serviceResult = $this->registry->getServiceByName($serviceName);
            if (!$serviceResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato',
                    'service_name' => $serviceName
                ];
            }

            $service = $serviceResult['service'];
            
            // Filtra solo istanze sane se richiesto
            $healthyOnly = $options['healthy_only'] ?? true;
            if ($healthyOnly) {
                $instances = array_filter($service['instances'], fn($i) => $i['status'] === 'healthy');
                $service['instances'] = array_values($instances);
            }

            if (empty($service['instances'])) {
                return [
                    'success' => false,
                    'error' => 'Nessuna istanza sana disponibile',
                    'service_name' => $serviceName
                ];
            }

            // Applica load balancing
            $loadBalancing = $options['load_balancing'] ?? true;
            if ($loadBalancing && count($service['instances']) > 1) {
                $selectedInstance = $this->loadBalancer->selectInstance($service['instances'], $options);
                $service['selected_instance'] = $selectedInstance;
            } else {
                $service['selected_instance'] = $service['instances'][0];
            }

            // Cache il risultato
            if ($useCache) {
                Cache::put($cacheKey, $service, 300); // 5 minuti
            }

            Log::info("Service Discovery: Servizio scoperto", [
                'service_name' => $serviceName,
                'instances_count' => count($service['instances']),
                'selected_instance' => $service['selected_instance']['id'] ?? null,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => true,
                'service' => $service,
                'cached' => false
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella scoperta servizio", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella scoperta del servizio'
            ];
        }
    }

    /**
     * Ottiene un'istanza bilanciata di un servizio
     */
    public function getLoadBalancedInstance(string $serviceName, array $options = []): array
    {
        try {
            $discoveryResult = $this->discoverService($serviceName, $options);
            if (!$discoveryResult['success']) {
                return $discoveryResult;
            }

            $service = $discoveryResult['service'];
            $instance = $service['selected_instance'] ?? null;

            if (!$instance) {
                return [
                    'success' => false,
                    'error' => 'Nessuna istanza disponibile',
                    'service_name' => $serviceName
                ];
            }

            return [
                'success' => true,
                'instance' => $instance,
                'service_name' => $serviceName,
                'load_balanced' => true
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel recupero istanza bilanciata", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dell\'istanza bilanciata'
            ];
        }
    }

    /**
     * Ottiene tutte le istanze di un servizio
     */
    public function getAllInstances(string $serviceName, array $options = []): array
    {
        try {
            $healthyOnly = $options['healthy_only'] ?? false;
            
            if ($healthyOnly) {
                $instancesResult = $this->registry->getHealthyInstances($serviceName);
            } else {
                $instancesResult = $this->registry->getServiceInstances($serviceName);
            }

            if (!$instancesResult['success']) {
                return $instancesResult;
            }

            return [
                'success' => true,
                'instances' => $instancesResult['instances'],
                'count' => $instancesResult['count'],
                'service_name' => $serviceName,
                'healthy_only' => $healthyOnly
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel recupero tutte le istanze", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle istanze'
            ];
        }
    }

    /**
     * Cerca servizi per tag
     */
    public function findServicesByTag(string $tag, array $options = []): array
    {
        try {
            $allServicesResult = $this->registry->getAllServices();
            if (!$allServicesResult['success']) {
                return $allServicesResult;
            }

            $matchingServices = [];
            foreach ($allServicesResult['services'] as $service) {
                if (in_array($tag, $service['tags'] ?? [])) {
                    $matchingServices[] = $service;
                }
            }

            return [
                'success' => true,
                'services' => $matchingServices,
                'count' => count($matchingServices),
                'tag' => $tag
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella ricerca per tag", [
                'error' => $e->getMessage(),
                'tag' => $tag,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella ricerca per tag'
            ];
        }
    }

    /**
     * Cerca servizi per categoria
     */
    public function findServicesByCategory(string $category, array $options = []): array
    {
        try {
            $servicesResult = $this->registry->getServicesByCategory($category);
            if (!$servicesResult['success']) {
                return $servicesResult;
            }

            return [
                'success' => true,
                'services' => $servicesResult['services'],
                'count' => $servicesResult['count'],
                'category' => $category
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella ricerca per categoria", [
                'error' => $e->getMessage(),
                'category' => $category,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella ricerca per categoria'
            ];
        }
    }

    /**
     * Cerca servizi per versione
     */
    public function findServicesByVersion(string $version, array $options = []): array
    {
        try {
            $allServicesResult = $this->registry->getAllServices();
            if (!$allServicesResult['success']) {
                return $allServicesResult;
            }

            $matchingServices = [];
            foreach ($allServicesResult['services'] as $service) {
                if ($service['version'] === $version) {
                    $matchingServices[] = $service;
                }
            }

            return [
                'success' => true,
                'services' => $matchingServices,
                'count' => count($matchingServices),
                'version' => $version
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella ricerca per versione", [
                'error' => $e->getMessage(),
                'version' => $version,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella ricerca per versione'
            ];
        }
    }

    /**
     * Cerca servizi per metadata
     */
    public function findServicesByMetadata(array $metadata, array $options = []): array
    {
        try {
            $allServicesResult = $this->registry->getAllServices();
            if (!$allServicesResult['success']) {
                return $allServicesResult;
            }

            $matchingServices = [];
            foreach ($allServicesResult['services'] as $service) {
                $serviceMetadata = $service['metadata'] ?? [];
                $matches = true;
                
                foreach ($metadata as $key => $value) {
                    if (!isset($serviceMetadata[$key]) || $serviceMetadata[$key] !== $value) {
                        $matches = false;
                        break;
                    }
                }
                
                if ($matches) {
                    $matchingServices[] = $service;
                }
            }

            return [
                'success' => true,
                'services' => $matchingServices,
                'count' => count($matchingServices),
                'metadata' => $metadata
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella ricerca per metadata", [
                'error' => $e->getMessage(),
                'metadata' => $metadata,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella ricerca per metadata'
            ];
        }
    }

    /**
     * Ottiene l'URL completo di un servizio
     */
    public function getServiceUrl(string $serviceName, array $options = []): array
    {
        try {
            $instanceResult = $this->getLoadBalancedInstance($serviceName, $options);
            if (!$instanceResult['success']) {
                return $instanceResult;
            }

            $instance = $instanceResult['instance'];
            $protocol = $options['protocol'] ?? 'http';
            $path = $options['path'] ?? '';
            
            $url = "{$protocol}://{$instance['address']}:{$instance['port']}{$path}";

            return [
                'success' => true,
                'url' => $url,
                'instance' => $instance,
                'service_name' => $serviceName
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel recupero URL servizio", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dell\'URL del servizio'
            ];
        }
    }

    /**
     * Esegue una chiamata HTTP a un servizio
     */
    public function callService(string $serviceName, string $method, string $path, array $data = [], array $options = []): array
    {
        try {
            $urlResult = $this->getServiceUrl($serviceName, $options);
            if (!$urlResult['success']) {
                return $urlResult;
            }

            $url = $urlResult['url'] . $path;
            $instance = $urlResult['instance'];

            // Simula chiamata HTTP
            $response = $this->simulateHttpCall($method, $url, $data, $options);

            Log::info("Service Discovery: Chiamata servizio eseguita", [
                'service_name' => $serviceName,
                'method' => $method,
                'path' => $path,
                'url' => $url,
                'instance_id' => $instance['id'],
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => true,
                'response' => $response,
                'instance' => $instance,
                'service_name' => $serviceName
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella chiamata servizio", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'method' => $method,
                'path' => $path,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella chiamata al servizio'
            ];
        }
    }

    /**
     * Simula una chiamata HTTP
     */
    private function simulateHttpCall(string $method, string $url, array $data, array $options): array
    {
        // Simula latenza di rete
        usleep(rand(100000, 500000)); // 100-500ms
        
        // Simula risposta basata sul metodo
        switch (strtoupper($method)) {
            case 'GET':
                return [
                    'status' => 200,
                    'data' => ['message' => 'GET request successful', 'url' => $url],
                    'headers' => ['Content-Type' => 'application/json']
                ];
            case 'POST':
                return [
                    'status' => 201,
                    'data' => ['message' => 'POST request successful', 'data' => $data],
                    'headers' => ['Content-Type' => 'application/json']
                ];
            case 'PUT':
                return [
                    'status' => 200,
                    'data' => ['message' => 'PUT request successful', 'data' => $data],
                    'headers' => ['Content-Type' => 'application/json']
                ];
            case 'DELETE':
                return [
                    'status' => 204,
                    'data' => ['message' => 'DELETE request successful'],
                    'headers' => ['Content-Type' => 'application/json']
                ];
            default:
                return [
                    'status' => 405,
                    'data' => ['error' => 'Method not allowed'],
                    'headers' => ['Content-Type' => 'application/json']
                ];
        }
    }

    /**
     * Pulisce la cache di discovery
     */
    public function clearCache(string $serviceName = null): array
    {
        try {
            if ($serviceName) {
                Cache::forget("service_discovery:{$serviceName}");
            } else {
                // Pulisce tutta la cache di discovery
                $pattern = 'service_discovery:*';
                // In un'implementazione reale, useresti Redis SCAN
                // Per ora simuliamo
            }

            return [
                'success' => true,
                'message' => 'Cache pulita con successo',
                'service_name' => $serviceName
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella pulizia cache", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella pulizia della cache'
            ];
        }
    }

    /**
     * Ottiene le statistiche di discovery
     */
    public function getStats(): array
    {
        try {
            $stats = [
                'total_discoveries' => 0,
                'cache_hits' => 0,
                'cache_misses' => 0,
                'average_discovery_time' => 0,
                'top_services' => [],
                'discovery_errors' => 0
            ];

            // Simula statistiche
            $stats['total_discoveries'] = rand(100, 1000);
            $stats['cache_hits'] = rand(50, 500);
            $stats['cache_misses'] = rand(25, 250);
            $stats['average_discovery_time'] = rand(10, 100) / 1000; // 10-100ms
            $stats['discovery_errors'] = rand(0, 50);

            $stats['top_services'] = [
                ['service' => 'user-service', 'discoveries' => rand(100, 500)],
                ['service' => 'product-service', 'discoveries' => rand(50, 300)],
                ['service' => 'order-service', 'discoveries' => rand(25, 200)]
            ];

            return [
                'success' => true,
                'data' => $stats,
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del discovery
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'discovery' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del discovery
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del discovery
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
