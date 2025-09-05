<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Service;
use App\Models\ServiceInstance;
use App\Models\ServiceHealth;

class ServiceRegistry
{
    protected $serviceId = 'service-registry';
    protected $version = '1.0.0';
    protected $services = [];
    protected $instances = [];
    protected $healthChecks = [];

    public function __construct()
    {
        $this->loadServicesFromCache();
    }

    /**
     * Registra un servizio nel registry
     */
    public function registerService(array $serviceData): array
    {
        try {
            $serviceId = $serviceData['id'] ?? uniqid('service_');
            $serviceName = $serviceData['name'];
            $address = $serviceData['address'];
            $port = $serviceData['port'];
            $version = $serviceData['version'] ?? '1.0.0';
            $category = $serviceData['category'] ?? 'default';
            $tags = $serviceData['tags'] ?? [];
            $metadata = $serviceData['metadata'] ?? [];

            // Crea o aggiorna il servizio
            $service = [
                'id' => $serviceId,
                'name' => $serviceName,
                'version' => $version,
                'category' => $category,
                'tags' => $tags,
                'metadata' => $metadata,
                'instances' => [],
                'registered_at' => now()->toISOString(),
                'last_updated' => now()->toISOString()
            ];

            // Aggiungi istanza
            $instance = [
                'id' => uniqid('instance_'),
                'service_id' => $serviceId,
                'address' => $address,
                'port' => $port,
                'status' => 'healthy',
                'registered_at' => now()->toISOString(),
                'last_heartbeat' => now()->toISOString(),
                'metadata' => $metadata
            ];

            $this->services[$serviceId] = $service;
            $this->instances[$instance['id']] = $instance;
            $this->services[$serviceId]['instances'][] = $instance['id'];

            // Cache il servizio
            $this->cacheService($serviceId);

            Log::info("Service Registry: Servizio registrato", [
                'service_id' => $serviceId,
                'service_name' => $serviceName,
                'address' => $address,
                'port' => $port,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => true,
                'service_id' => $serviceId,
                'instance_id' => $instance['id'],
                'message' => 'Servizio registrato con successo'
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nella registrazione servizio", [
                'error' => $e->getMessage(),
                'service_data' => $serviceData,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella registrazione del servizio'
            ];
        }
    }

    /**
     * Deregistra un servizio dal registry
     */
    public function deregisterService(string $serviceId): array
    {
        try {
            if (!isset($this->services[$serviceId])) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato'
                ];
            }

            $service = $this->services[$serviceId];
            
            // Rimuovi tutte le istanze del servizio
            foreach ($service['instances'] as $instanceId) {
                unset($this->instances[$instanceId]);
            }

            // Rimuovi il servizio
            unset($this->services[$serviceId]);

            // Rimuovi dalla cache
            Cache::forget("service_registry:{$serviceId}");

            Log::info("Service Registry: Servizio deregistrato", [
                'service_id' => $serviceId,
                'service_name' => $service['name'],
                'registry' => $this->serviceId
            ]);

            return [
                'success' => true,
                'message' => 'Servizio deregistrato con successo'
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nella deregistrazione servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella deregistrazione del servizio'
            ];
        }
    }

    /**
     * Aggiorna l'heartbeat di un servizio
     */
    public function updateHeartbeat(string $serviceId, string $instanceId = null): array
    {
        try {
            if ($instanceId) {
                // Aggiorna istanza specifica
                if (isset($this->instances[$instanceId])) {
                    $this->instances[$instanceId]['last_heartbeat'] = now()->toISOString();
                    $this->instances[$instanceId]['status'] = 'healthy';
                    
                    // Aggiorna cache
                    $this->cacheService($serviceId);
                    
                    return [
                        'success' => true,
                        'message' => 'Heartbeat aggiornato per istanza'
                    ];
                }
            } else {
                // Aggiorna tutte le istanze del servizio
                if (isset($this->services[$serviceId])) {
                    foreach ($this->services[$serviceId]['instances'] as $instanceId) {
                        if (isset($this->instances[$instanceId])) {
                            $this->instances[$instanceId]['last_heartbeat'] = now()->toISOString();
                            $this->instances[$instanceId]['status'] = 'healthy';
                        }
                    }
                    
                    // Aggiorna cache
                    $this->cacheService($serviceId);
                    
                    return [
                        'success' => true,
                        'message' => 'Heartbeat aggiornato per servizio'
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Servizio o istanza non trovata'
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nell'aggiornamento heartbeat", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'instance_id' => $instanceId,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nell\'aggiornamento heartbeat'
            ];
        }
    }

    /**
     * Ottiene un servizio per ID
     */
    public function getService(string $serviceId): array
    {
        try {
            if (!isset($this->services[$serviceId])) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato'
                ];
            }

            $service = $this->services[$serviceId];
            
            // Aggiungi istanze complete
            $service['instances'] = array_map(function($instanceId) {
                return $this->instances[$instanceId] ?? null;
            }, $service['instances']);
            
            $service['instances'] = array_filter($service['instances']);

            return [
                'success' => true,
                'service' => $service
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero del servizio'
            ];
        }
    }

    /**
     * Ottiene un servizio per nome
     */
    public function getServiceByName(string $serviceName): array
    {
        try {
            foreach ($this->services as $serviceId => $service) {
                if ($service['name'] === $serviceName) {
                    return $this->getService($serviceId);
                }
            }

            return [
                'success' => false,
                'error' => 'Servizio non trovato'
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero servizio per nome", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero del servizio'
            ];
        }
    }

    /**
     * Ottiene tutte le istanze di un servizio
     */
    public function getServiceInstances(string $serviceName): array
    {
        try {
            $serviceResult = $this->getServiceByName($serviceName);
            if (!$serviceResult['success']) {
                return $serviceResult;
            }

            $instances = $serviceResult['service']['instances'] ?? [];

            return [
                'success' => true,
                'instances' => $instances,
                'count' => count($instances)
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero istanze servizio", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle istanze'
            ];
        }
    }

    /**
     * Ottiene solo le istanze sane di un servizio
     */
    public function getHealthyInstances(string $serviceName): array
    {
        try {
            $instancesResult = $this->getServiceInstances($serviceName);
            if (!$instancesResult['success']) {
                return $instancesResult;
            }

            $healthyInstances = array_filter($instancesResult['instances'], function($instance) {
                return $instance['status'] === 'healthy';
            });

            return [
                'success' => true,
                'instances' => array_values($healthyInstances),
                'count' => count($healthyInstances)
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero istanze sane", [
                'error' => $e->getMessage(),
                'service_name' => $serviceName,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle istanze sane'
            ];
        }
    }

    /**
     * Ottiene tutti i servizi registrati
     */
    public function getAllServices(): array
    {
        try {
            $services = [];
            foreach ($this->services as $serviceId => $service) {
                $service['instances'] = array_map(function($instanceId) {
                    return $this->instances[$instanceId] ?? null;
                }, $service['instances']);
                
                $service['instances'] = array_filter($service['instances']);
                $services[] = $service;
            }

            return [
                'success' => true,
                'services' => $services,
                'count' => count($services)
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero tutti i servizi", [
                'error' => $e->getMessage(),
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi'
            ];
        }
    }

    /**
     * Ottiene i servizi per categoria
     */
    public function getServicesByCategory(string $category): array
    {
        try {
            $services = [];
            foreach ($this->services as $serviceId => $service) {
                if ($service['category'] === $category) {
                    $service['instances'] = array_map(function($instanceId) {
                        return $this->instances[$instanceId] ?? null;
                    }, $service['instances']);
                    
                    $service['instances'] = array_filter($service['instances']);
                    $services[] = $service;
                }
            }

            return [
                'success' => true,
                'services' => $services,
                'count' => count($services),
                'category' => $category
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero servizi per categoria", [
                'error' => $e->getMessage(),
                'category' => $category,
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi per categoria'
            ];
        }
    }

    /**
     * Ottiene le categorie disponibili
     */
    public function getCategories(): array
    {
        try {
            $categories = [];
            foreach ($this->services as $service) {
                $category = $service['category'];
                if (!in_array($category, $categories)) {
                    $categories[] = $category;
                }
            }

            return [
                'success' => true,
                'categories' => $categories,
                'count' => count($categories)
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nel recupero categorie", [
                'error' => $e->getMessage(),
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle categorie'
            ];
        }
    }

    /**
     * Cache un servizio
     */
    private function cacheService(string $serviceId): void
    {
        try {
            if (isset($this->services[$serviceId])) {
                Cache::put("service_registry:{$serviceId}", $this->services[$serviceId], 3600);
            }
        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Carica i servizi dalla cache
     */
    private function loadServicesFromCache(): void
    {
        try {
            $cachedServices = Cache::get('service_registry:all', []);
            if (!empty($cachedServices)) {
                $this->services = $cachedServices;
            }
        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Salva tutti i servizi nella cache
     */
    private function saveServicesToCache(): void
    {
        try {
            Cache::put('service_registry:all', $this->services, 3600);
        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Pulisce i servizi non attivi
     */
    public function cleanupInactiveServices(int $timeoutMinutes = 5): array
    {
        try {
            $cleaned = 0;
            $cutoff = now()->subMinutes($timeoutMinutes);
            
            foreach ($this->instances as $instanceId => $instance) {
                $lastHeartbeat = \Carbon\Carbon::parse($instance['last_heartbeat']);
                if ($lastHeartbeat->lt($cutoff)) {
                    // Marca come non attivo
                    $this->instances[$instanceId]['status'] = 'inactive';
                    $cleaned++;
                }
            }
            
            return [
                'success' => true,
                'cleaned' => $cleaned,
                'message' => "Puliti {$cleaned} servizi non attivi"
            ];

        } catch (\Exception $e) {
            Log::error("Service Registry: Errore nella pulizia servizi", [
                'error' => $e->getMessage(),
                'registry' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella pulizia dei servizi'
            ];
        }
    }

    /**
     * Ottiene le statistiche del registry
     */
    public function getStats(): array
    {
        try {
            $totalServices = count($this->services);
            $totalInstances = count($this->instances);
            $healthyInstances = count(array_filter($this->instances, fn($i) => $i['status'] === 'healthy'));
            $inactiveInstances = $totalInstances - $healthyInstances;
            
            $categories = $this->getCategories();
            $categoryCount = $categories['success'] ? $categories['count'] : 0;

            return [
                'success' => true,
                'stats' => [
                    'total_services' => $totalServices,
                    'total_instances' => $totalInstances,
                    'healthy_instances' => $healthyInstances,
                    'inactive_instances' => $inactiveInstances,
                    'categories' => $categoryCount,
                    'uptime' => '100%', // Simulato
                    'last_cleanup' => now()->toISOString()
                ],
                'registry' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'registry' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del registry
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'registry' => $this->serviceId,
                'version' => $this->version,
                'services_count' => count($this->services),
                'instances_count' => count($this->instances),
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'registry' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del registry
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del registry
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
