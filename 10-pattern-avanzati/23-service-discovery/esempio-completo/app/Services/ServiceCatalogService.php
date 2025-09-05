<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceRegistry;

class ServiceCatalogService
{
    protected $serviceId = 'service-catalog-service';
    protected $version = '1.0.0';
    protected $registry;
    protected $catalog = [];

    public function __construct(ServiceRegistry $registry)
    {
        $this->registry = $registry;
        $this->loadCatalog();
    }

    /**
     * Ottiene il catalogo completo dei servizi
     */
    public function getServiceCatalog(): array
    {
        try {
            $catalog = [
                'categories' => $this->getCategories(),
                'services' => $this->getAllServices(),
                'total_services' => 0,
                'total_instances' => 0,
                'last_updated' => now()->toISOString()
            ];

            // Calcola totali
            foreach ($catalog['services'] as $service) {
                $catalog['total_services']++;
                $catalog['total_instances'] += count($service['instances'] ?? []);
            }

            return [
                'success' => true,
                'catalog' => $catalog,
                'catalog_service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero catalogo", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero del catalogo'
            ];
        }
    }

    /**
     * Ottiene le categorie dei servizi
     */
    public function getCategories(): array
    {
        try {
            $categoriesResult = $this->registry->getCategories();
            if (!$categoriesResult['success']) {
                return [];
            }

            $categories = [];
            foreach ($categoriesResult['categories'] as $category) {
                $categories[] = [
                    'name' => $category,
                    'display_name' => ucfirst(str_replace('_', ' ', $category)),
                    'description' => $this->getCategoryDescription($category),
                    'services_count' => $this->getServicesCountByCategory($category),
                    'icon' => $this->getCategoryIcon($category)
                ];
            }

            return $categories;

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero categorie", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [];
        }
    }

    /**
     * Ottiene tutti i servizi
     */
    private function getAllServices(): array
    {
        try {
            $servicesResult = $this->registry->getAllServices();
            if (!$servicesResult['success']) {
                return [];
            }

            $services = [];
            foreach ($servicesResult['services'] as $service) {
                $services[] = [
                    'id' => $service['id'],
                    'name' => $service['name'],
                    'version' => $service['version'],
                    'category' => $service['category'],
                    'tags' => $service['tags'] ?? [],
                    'metadata' => $service['metadata'] ?? [],
                    'instances' => $service['instances'] ?? [],
                    'instances_count' => count($service['instances'] ?? []),
                    'healthy_instances' => count(array_filter($service['instances'] ?? [], fn($i) => $i['status'] === 'healthy')),
                    'status' => $this->getServiceStatus($service),
                    'description' => $this->getServiceDescription($service['name']),
                    'documentation_url' => $this->getServiceDocumentationUrl($service['name']),
                    'registered_at' => $service['registered_at'] ?? null,
                    'last_updated' => $service['last_updated'] ?? null
                ];
            }

            return $services;

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero servizi", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [];
        }
    }

    /**
     * Ottiene i servizi per categoria
     */
    public function getServicesByCategory(string $category): array
    {
        try {
            $servicesResult = $this->registry->getServicesByCategory($category);
            if (!$servicesResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Categoria non trovata',
                    'category' => $category
                ];
            }

            $services = [];
            foreach ($servicesResult['services'] as $service) {
                $services[] = [
                    'id' => $service['id'],
                    'name' => $service['name'],
                    'version' => $service['version'],
                    'category' => $service['category'],
                    'tags' => $service['tags'] ?? [],
                    'metadata' => $service['metadata'] ?? [],
                    'instances' => $service['instances'] ?? [],
                    'instances_count' => count($service['instances'] ?? []),
                    'healthy_instances' => count(array_filter($service['instances'] ?? [], fn($i) => $i['status'] === 'healthy')),
                    'status' => $this->getServiceStatus($service),
                    'description' => $this->getServiceDescription($service['name']),
                    'documentation_url' => $this->getServiceDocumentationUrl($service['name'])
                ];
            }

            return [
                'success' => true,
                'services' => $services,
                'count' => count($services),
                'category' => $category
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero servizi per categoria", [
                'error' => $e->getMessage(),
                'category' => $category,
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi per categoria'
            ];
        }
    }

    /**
     * Cerca servizi nel catalogo
     */
    public function searchServices(string $query, array $options = []): array
    {
        try {
            $allServices = $this->getAllServices();
            $results = [];
            $query = strtolower($query);

            foreach ($allServices as $service) {
                $matches = false;

                // Cerca nel nome
                if (str_contains(strtolower($service['name']), $query)) {
                    $matches = true;
                }

                // Cerca nella descrizione
                if (str_contains(strtolower($service['description']), $query)) {
                    $matches = true;
                }

                // Cerca nei tag
                foreach ($service['tags'] as $tag) {
                    if (str_contains(strtolower($tag), $query)) {
                        $matches = true;
                        break;
                    }
                }

                // Cerca nella categoria
                if (str_contains(strtolower($service['category']), $query)) {
                    $matches = true;
                }

                if ($matches) {
                    $results[] = $service;
                }
            }

            // Ordina per rilevanza (nome prima, poi descrizione)
            usort($results, function($a, $b) use ($query) {
                $aName = strtolower($a['name']);
                $bName = strtolower($b['name']);
                
                if (str_starts_with($aName, $query) && !str_starts_with($bName, $query)) {
                    return -1;
                } elseif (!str_starts_with($aName, $query) && str_starts_with($bName, $query)) {
                    return 1;
                }
                
                return strcmp($aName, $bName);
            });

            return [
                'success' => true,
                'results' => $results,
                'count' => count($results),
                'query' => $query
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nella ricerca servizi", [
                'error' => $e->getMessage(),
                'query' => $query,
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella ricerca dei servizi'
            ];
        }
    }

    /**
     * Ottiene i servizi più popolari
     */
    public function getPopularServices(int $limit = 10): array
    {
        try {
            $allServices = $this->getAllServices();
            
            // Ordina per numero di istanze sane
            usort($allServices, function($a, $b) {
                return $b['healthy_instances'] - $a['healthy_instances'];
            });

            $popular = array_slice($allServices, 0, $limit);

            return [
                'success' => true,
                'services' => $popular,
                'count' => count($popular),
                'limit' => $limit
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero servizi popolari", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi popolari'
            ];
        }
    }

    /**
     * Ottiene i servizi recenti
     */
    public function getRecentServices(int $limit = 10): array
    {
        try {
            $allServices = $this->getAllServices();
            
            // Ordina per data di registrazione
            usort($allServices, function($a, $b) {
                $aTime = strtotime($a['registered_at'] ?? '1970-01-01');
                $bTime = strtotime($b['registered_at'] ?? '1970-01-01');
                return $bTime - $aTime;
            });

            $recent = array_slice($allServices, 0, $limit);

            return [
                'success' => true,
                'services' => $recent,
                'count' => count($recent),
                'limit' => $limit
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero servizi recenti", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi recenti'
            ];
        }
    }

    /**
     * Ottiene le statistiche del catalogo
     */
    public function getCatalogStats(): array
    {
        try {
            $allServices = $this->getAllServices();
            $categories = $this->getCategories();

            $stats = [
                'total_services' => count($allServices),
                'total_categories' => count($categories),
                'total_instances' => 0,
                'healthy_instances' => 0,
                'services_by_category' => [],
                'services_by_status' => [],
                'top_tags' => [],
                'version_distribution' => []
            ];

            foreach ($allServices as $service) {
                $stats['total_instances'] += $service['instances_count'];
                $stats['healthy_instances'] += $service['healthy_instances'];

                // Per categoria
                $category = $service['category'];
                $stats['services_by_category'][$category] = ($stats['services_by_category'][$category] ?? 0) + 1;

                // Per status
                $status = $service['status'];
                $stats['services_by_status'][$status] = ($stats['services_by_status'][$status] ?? 0) + 1;

                // Per versione
                $version = $service['version'];
                $stats['version_distribution'][$version] = ($stats['version_distribution'][$version] ?? 0) + 1;

                // Per tag
                foreach ($service['tags'] as $tag) {
                    $stats['top_tags'][$tag] = ($stats['top_tags'][$tag] ?? 0) + 1;
                }
            }

            // Ordina i tag più popolari
            arsort($stats['top_tags']);
            $stats['top_tags'] = array_slice($stats['top_tags'], 0, 10, true);

            return [
                'success' => true,
                'stats' => $stats,
                'catalog_service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nel recupero statistiche catalogo", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle statistiche del catalogo'
            ];
        }
    }

    /**
     * Ottiene la descrizione di una categoria
     */
    private function getCategoryDescription(string $category): string
    {
        $descriptions = [
            'api' => 'Servizi API per comunicazione tra sistemi',
            'database' => 'Servizi di gestione database e persistenza',
            'auth' => 'Servizi di autenticazione e autorizzazione',
            'notification' => 'Servizi di notifiche e comunicazioni',
            'payment' => 'Servizi di pagamento e transazioni',
            'storage' => 'Servizi di storage e file management',
            'monitoring' => 'Servizi di monitoring e logging',
            'default' => 'Servizi generici'
        ];

        return $descriptions[$category] ?? $descriptions['default'];
    }

    /**
     * Ottiene l'icona di una categoria
     */
    private function getCategoryIcon(string $category): string
    {
        $icons = [
            'api' => '🔌',
            'database' => '🗄️',
            'auth' => '🔐',
            'notification' => '📢',
            'payment' => '💳',
            'storage' => '💾',
            'monitoring' => '📊',
            'default' => '⚙️'
        ];

        return $icons[$category] ?? $icons['default'];
    }

    /**
     * Ottiene il conteggio dei servizi per categoria
     */
    private function getServicesCountByCategory(string $category): int
    {
        try {
            $servicesResult = $this->registry->getServicesByCategory($category);
            return $servicesResult['success'] ? $servicesResult['count'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Ottiene lo status di un servizio
     */
    private function getServiceStatus(array $service): string
    {
        $instances = $service['instances'] ?? [];
        if (empty($instances)) {
            return 'no_instances';
        }

        $healthyCount = count(array_filter($instances, fn($i) => $i['status'] === 'healthy'));
        $totalCount = count($instances);

        if ($healthyCount === 0) {
            return 'unhealthy';
        } elseif ($healthyCount === $totalCount) {
            return 'healthy';
        } else {
            return 'degraded';
        }
    }

    /**
     * Ottiene la descrizione di un servizio
     */
    private function getServiceDescription(string $serviceName): string
    {
        $descriptions = [
            'user-service' => 'Servizio per la gestione degli utenti e autenticazione',
            'product-service' => 'Servizio per il catalogo prodotti e inventario',
            'order-service' => 'Servizio per la gestione degli ordini e carrello',
            'payment-service' => 'Servizio per il processing dei pagamenti',
            'notification-service' => 'Servizio per l\'invio di notifiche',
            'email-service' => 'Servizio per l\'invio di email',
            'sms-service' => 'Servizio per l\'invio di SMS',
            'file-service' => 'Servizio per la gestione dei file',
            'image-service' => 'Servizio per il processing delle immagini',
            'search-service' => 'Servizio per la ricerca e indicizzazione'
        ];

        return $descriptions[$serviceName] ?? 'Servizio generico';
    }

    /**
     * Ottiene l'URL della documentazione di un servizio
     */
    private function getServiceDocumentationUrl(string $serviceName): string
    {
        return "/docs/services/{$serviceName}";
    }

    /**
     * Carica il catalogo dalla cache
     */
    private function loadCatalog(): void
    {
        try {
            $cached = Cache::get('service_catalog:all');
            if ($cached) {
                $this->catalog = $cached;
            }
        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Salva il catalogo nella cache
     */
    private function saveCatalog(): void
    {
        try {
            Cache::put('service_catalog:all', $this->catalog, 3600); // 1 ora
        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Aggiorna il catalogo
     */
    public function refreshCatalog(): array
    {
        try {
            $this->loadCatalog();
            $this->saveCatalog();

            return [
                'success' => true,
                'message' => 'Catalogo aggiornato con successo',
                'catalog_service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Service Catalog Service: Errore nell'aggiornamento catalogo", [
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nell\'aggiornamento del catalogo'
            ];
        }
    }

    /**
     * Health check del catalogo
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'catalog_service' => $this->serviceId,
                'version' => $this->version,
                'catalog_loaded' => !empty($this->catalog),
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'catalog_service' => $this->serviceId,
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
