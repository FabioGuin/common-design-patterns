<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ServiceDiscoveryService
{
    protected $serviceId = 'service-discovery';
    protected $version = '1.0.0';
    protected $services = [];

    public function __construct()
    {
        $this->initializeServices();
    }

    /**
     * Inizializza i servizi registrati
     */
    private function initializeServices(): void
    {
        $this->services = [
            'user-service' => [
                'id' => 'user-service',
                'name' => 'User Service',
                'version' => '1.0.0',
                'host' => 'localhost',
                'port' => 8001,
                'protocol' => 'http',
                'health_endpoint' => '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ],
            'product-service' => [
                'id' => 'product-service',
                'name' => 'Product Service',
                'version' => '1.0.0',
                'host' => 'localhost',
                'port' => 8002,
                'protocol' => 'http',
                'health_endpoint' => '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ],
            'order-service' => [
                'id' => 'order-service',
                'name' => 'Order Service',
                'version' => '1.0.0',
                'host' => 'localhost',
                'port' => 8003,
                'protocol' => 'http',
                'health_endpoint' => '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ],
            'payment-service' => [
                'id' => 'payment-service',
                'name' => 'Payment Service',
                'version' => '1.0.0',
                'host' => 'localhost',
                'port' => 8004,
                'protocol' => 'http',
                'health_endpoint' => '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ],
            'api-gateway' => [
                'id' => 'api-gateway',
                'name' => 'API Gateway',
                'version' => '1.0.0',
                'host' => 'localhost',
                'port' => 8000,
                'protocol' => 'http',
                'health_endpoint' => '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Registra un nuovo servizio
     */
    public function registerService(array $serviceData): array
    {
        try {
            $serviceId = $serviceData['id'] ?? uniqid('service_');
            
            $service = [
                'id' => $serviceId,
                'name' => $serviceData['name'] ?? 'Unknown Service',
                'version' => $serviceData['version'] ?? '1.0.0',
                'host' => $serviceData['host'] ?? 'localhost',
                'port' => $serviceData['port'] ?? 8000,
                'protocol' => $serviceData['protocol'] ?? 'http',
                'health_endpoint' => $serviceData['health_endpoint'] ?? '/health',
                'status' => 'unknown',
                'last_check' => null,
                'registered_at' => now()->toISOString()
            ];

            $this->services[$serviceId] = $service;
            
            // Cache del servizio
            Cache::put("service:{$serviceId}", $service, 3600);

            Log::info("Service Discovery: Servizio registrato", [
                'service_id' => $serviceId,
                'service_name' => $service['name'],
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $service,
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella registrazione servizio", [
                'error' => $e->getMessage(),
                'service_data' => $serviceData,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Deregistra un servizio
     */
    public function deregisterService(string $serviceId): array
    {
        try {
            if (!isset($this->services[$serviceId])) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato',
                    'discovery' => $this->serviceId
                ];
            }

            unset($this->services[$serviceId]);
            Cache::forget("service:{$serviceId}");

            Log::info("Service Discovery: Servizio deregistrato", [
                'service_id' => $serviceId,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => true,
                'message' => 'Servizio deregistrato con successo',
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella deregistrazione servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Trova un servizio per ID
     */
    public function findService(string $serviceId): array
    {
        try {
            // Prova prima la cache
            $cachedService = Cache::get("service:{$serviceId}");
            if ($cachedService) {
                return [
                    'success' => true,
                    'data' => $cachedService,
                    'discovery' => $this->serviceId,
                    'cached' => true
                ];
            }

            // Cerca nei servizi registrati
            if (isset($this->services[$serviceId])) {
                $service = $this->services[$serviceId];
                Cache::put("service:{$serviceId}", $service, 3600);
                
                return [
                    'success' => true,
                    'data' => $service,
                    'discovery' => $this->serviceId
                ];
            }

            return [
                'success' => false,
                'error' => 'Servizio non trovato',
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nella ricerca servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Lista tutti i servizi
     */
    public function listServices(array $filters = []): array
    {
        try {
            $services = $this->services;

            // Applica filtri
            if (isset($filters['status'])) {
                $services = array_filter($services, function($service) use ($filters) {
                    return $service['status'] === $filters['status'];
                });
            }

            if (isset($filters['version'])) {
                $services = array_filter($services, function($service) use ($filters) {
                    return $service['version'] === $filters['version'];
                });
            }

            return [
                'success' => true,
                'data' => array_values($services),
                'count' => count($services),
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel recupero lista servizi", [
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Aggiorna lo status di un servizio
     */
    public function updateServiceStatus(string $serviceId, string $status): array
    {
        try {
            if (!isset($this->services[$serviceId])) {
                return [
                    'success' => false,
                    'error' => 'Servizio non trovato',
                    'discovery' => $this->serviceId
                ];
            }

            $this->services[$serviceId]['status'] = $status;
            $this->services[$serviceId]['last_check'] = now()->toISOString();

            // Aggiorna la cache
            Cache::put("service:{$serviceId}", $this->services[$serviceId], 3600);

            Log::info("Service Discovery: Status servizio aggiornato", [
                'service_id' => $serviceId,
                'new_status' => $status,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $this->services[$serviceId],
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nell'aggiornamento status", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Esegue health check di tutti i servizi
     */
    public function healthCheckAllServices(): array
    {
        try {
            $results = [];
            $healthyCount = 0;
            $totalCount = count($this->services);

            foreach ($this->services as $serviceId => $service) {
                $healthResult = $this->performHealthCheck($service);
                $results[$serviceId] = $healthResult;
                
                if ($healthResult['healthy']) {
                    $healthyCount++;
                }

                // Aggiorna lo status
                $this->updateServiceStatus($serviceId, $healthResult['healthy'] ? 'healthy' : 'unhealthy');
            }

            return [
                'success' => true,
                'data' => [
                    'total_services' => $totalCount,
                    'healthy_services' => $healthyCount,
                    'unhealthy_services' => $totalCount - $healthyCount,
                    'health_percentage' => $totalCount > 0 ? ($healthyCount / $totalCount) * 100 : 0,
                    'services' => $results,
                    'timestamp' => now()->toISOString()
                ],
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel health check servizi", [
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Esegue health check di un singolo servizio
     */
    private function performHealthCheck(array $service): array
    {
        try {
            $url = $service['protocol'] . '://' . $service['host'] . ':' . $service['port'] . $service['health_endpoint'];
            
            // Simula chiamata HTTP (in un'implementazione reale useresti Guzzle o cURL)
            $response = $this->simulateHttpCall($url);
            
            return [
                'service_id' => $service['id'],
                'service_name' => $service['name'],
                'url' => $url,
                'healthy' => $response['success'],
                'response_time' => $response['response_time'],
                'error' => $response['error'] ?? null,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'service_id' => $service['id'],
                'service_name' => $service['name'],
                'healthy' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Simula una chiamata HTTP
     */
    private function simulateHttpCall(string $url): array
    {
        // Simula latenza di rete
        $responseTime = rand(50, 500); // 50-500ms
        usleep($responseTime * 1000);

        // Simula successo/failure basato su URL
        $success = !str_contains($url, 'unhealthy');
        
        return [
            'success' => $success,
            'response_time' => $responseTime,
            'error' => $success ? null : 'Service unavailable'
        ];
    }

    /**
     * Ottiene statistiche del service discovery
     */
    public function getDiscoveryStats(): array
    {
        try {
            $totalServices = count($this->services);
            $healthyServices = collect($this->services)->where('status', 'healthy')->count();
            $unhealthyServices = collect($this->services)->where('status', 'unhealthy')->count();
            $unknownServices = collect($this->services)->where('status', 'unknown')->count();

            return [
                'success' => true,
                'data' => [
                    'total_services' => $totalServices,
                    'healthy_services' => $healthyServices,
                    'unhealthy_services' => $unhealthyServices,
                    'unknown_services' => $unknownServices,
                    'health_percentage' => $totalServices > 0 ? ($healthyServices / $totalServices) * 100 : 0
                ],
                'discovery' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Discovery: Errore nel recupero statistiche", [
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'discovery' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del service discovery
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'discovery' => $this->serviceId,
                'version' => $this->version,
                'registered_services' => count($this->services),
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
     * Ottiene l'ID del service discovery
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del service discovery
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
