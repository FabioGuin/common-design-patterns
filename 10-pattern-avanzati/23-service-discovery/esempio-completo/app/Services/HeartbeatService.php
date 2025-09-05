<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceRegistry;

class HeartbeatService
{
    protected $serviceId = 'heartbeat-service';
    protected $version = '1.0.0';
    protected $registry;
    protected $heartbeats = [];

    public function __construct(ServiceRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Invia heartbeat per un servizio
     */
    public function sendHeartbeat(string $serviceId, string $instanceId = null, array $metadata = []): array
    {
        try {
            $heartbeatData = [
                'service_id' => $serviceId,
                'instance_id' => $instanceId,
                'timestamp' => now()->toISOString(),
                'metadata' => $metadata,
                'heartbeat_service' => $this->serviceId
            ];

            // Aggiorna il registry
            $registryResult = $this->registry->updateHeartbeat($serviceId, $instanceId);
            if (!$registryResult['success']) {
                return $registryResult;
            }

            // Salva il heartbeat
            $this->saveHeartbeat($heartbeatData);

            Log::info("Heartbeat Service: Heartbeat inviato", [
                'service_id' => $serviceId,
                'instance_id' => $instanceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'message' => 'Heartbeat inviato con successo',
                'heartbeat' => $heartbeatData
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nell'invio heartbeat", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'instance_id' => $instanceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nell\'invio del heartbeat'
            ];
        }
    }

    /**
     * Salva un heartbeat
     */
    private function saveHeartbeat(array $heartbeatData): void
    {
        try {
            $serviceId = $heartbeatData['service_id'];
            $instanceId = $heartbeatData['instance_id'];
            
            $key = $instanceId ? "heartbeat:{$serviceId}:{$instanceId}" : "heartbeat:{$serviceId}";
            
            Cache::put($key, $heartbeatData, 3600); // 1 ora
            
            // Salva anche nella lista generale
            $this->heartbeats[] = $heartbeatData;
            
            // Mantieni solo gli ultimi 1000 heartbeat
            if (count($this->heartbeats) > 1000) {
                $this->heartbeats = array_slice($this->heartbeats, -1000);
            }

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nel salvataggio heartbeat", [
                'error' => $e->getMessage(),
                'heartbeat_service' => $this->serviceId
            ]);
        }
    }

    /**
     * Ottiene gli heartbeat di un servizio
     */
    public function getServiceHeartbeats(string $serviceId, int $limit = 100): array
    {
        try {
            $heartbeats = [];
            $pattern = "heartbeat:{$serviceId}:*";
            
            // In un'implementazione reale, useresti Redis SCAN
            // Per ora filtriamo dalla lista in memoria
            foreach ($this->heartbeats as $heartbeat) {
                if ($heartbeat['service_id'] === $serviceId) {
                    $heartbeats[] = $heartbeat;
                }
            }

            // Ordina per timestamp (più recenti prima)
            usort($heartbeats, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Limita i risultati
            $heartbeats = array_slice($heartbeats, 0, $limit);

            return [
                'success' => true,
                'heartbeats' => $heartbeats,
                'count' => count($heartbeats),
                'service_id' => $serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nel recupero heartbeat servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero degli heartbeat del servizio'
            ];
        }
    }

    /**
     * Ottiene l'ultimo heartbeat di un servizio
     */
    public function getLastHeartbeat(string $serviceId, string $instanceId = null): array
    {
        try {
            $key = $instanceId ? "heartbeat:{$serviceId}:{$instanceId}" : "heartbeat:{$serviceId}";
            $heartbeat = Cache::get($key);

            if (!$heartbeat) {
                return [
                    'success' => false,
                    'error' => 'Nessun heartbeat trovato',
                    'service_id' => $serviceId,
                    'instance_id' => $instanceId
                ];
            }

            return [
                'success' => true,
                'heartbeat' => $heartbeat,
                'service_id' => $serviceId,
                'instance_id' => $instanceId
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nel recupero ultimo heartbeat", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'instance_id' => $instanceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dell\'ultimo heartbeat'
            ];
        }
    }

    /**
     * Verifica se un servizio è attivo
     */
    public function isServiceActive(string $serviceId, int $timeoutMinutes = 5): array
    {
        try {
            $lastHeartbeatResult = $this->getLastHeartbeat($serviceId);
            if (!$lastHeartbeatResult['success']) {
                return [
                    'success' => true,
                    'active' => false,
                    'reason' => 'Nessun heartbeat trovato',
                    'service_id' => $serviceId
                ];
            }

            $heartbeat = $lastHeartbeatResult['heartbeat'];
            $lastHeartbeatTime = \Carbon\Carbon::parse($heartbeat['timestamp']);
            $timeout = now()->subMinutes($timeoutMinutes);

            $isActive = $lastHeartbeatTime->isAfter($timeout);

            return [
                'success' => true,
                'active' => $isActive,
                'last_heartbeat' => $heartbeat['timestamp'],
                'timeout_minutes' => $timeoutMinutes,
                'service_id' => $serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nella verifica attività servizio", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella verifica dell\'attività del servizio'
            ];
        }
    }

    /**
     * Ottiene i servizi non attivi
     */
    public function getInactiveServices(int $timeoutMinutes = 5): array
    {
        try {
            $allServicesResult = $this->registry->getAllServices();
            if (!$allServicesResult['success']) {
                return $allServicesResult;
            }

            $inactiveServices = [];
            foreach ($allServicesResult['services'] as $service) {
                $serviceId = $service['id'];
                $activityResult = $this->isServiceActive($serviceId, $timeoutMinutes);
                
                if ($activityResult['success'] && !$activityResult['active']) {
                    $inactiveServices[] = [
                        'service_id' => $serviceId,
                        'service_name' => $service['name'],
                        'last_heartbeat' => $activityResult['last_heartbeat'] ?? null,
                        'timeout_minutes' => $timeoutMinutes
                    ];
                }
            }

            return [
                'success' => true,
                'inactive_services' => $inactiveServices,
                'count' => count($inactiveServices),
                'timeout_minutes' => $timeoutMinutes
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nel recupero servizi non attivi", [
                'error' => $e->getMessage(),
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero dei servizi non attivi'
            ];
        }
    }

    /**
     * Pulisce i heartbeat vecchi
     */
    public function cleanupOldHeartbeats(int $days = 7): array
    {
        try {
            $cleaned = 0;
            $cutoff = now()->subDays($days);
            
            // Pulisce dalla cache
            $pattern = 'heartbeat:*';
            // In un'implementazione reale, useresti Redis SCAN
            // Per ora simuliamo
            
            // Pulisce dalla lista in memoria
            $this->heartbeats = array_filter($this->heartbeats, function($heartbeat) use ($cutoff) {
                $heartbeatTime = \Carbon\Carbon::parse($heartbeat['timestamp']);
                return $heartbeatTime->isAfter($cutoff);
            });

            return [
                'success' => true,
                'message' => "Puliti {$cleaned} heartbeat vecchi",
                'cleaned' => $cleaned,
                'days' => $days
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nella pulizia heartbeat vecchi", [
                'error' => $e->getMessage(),
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella pulizia dei heartbeat vecchi'
            ];
        }
    }

    /**
     * Ottiene le statistiche degli heartbeat
     */
    public function getHeartbeatStats(): array
    {
        try {
            $stats = [
                'total_heartbeats' => count($this->heartbeats),
                'active_services' => 0,
                'inactive_services' => 0,
                'average_heartbeat_interval' => 0,
                'top_services' => [],
                'heartbeat_trend' => []
            ];

            // Conta servizi attivi/inattivi
            $allServicesResult = $this->registry->getAllServices();
            if ($allServicesResult['success']) {
                foreach ($allServicesResult['services'] as $service) {
                    $activityResult = $this->isServiceActive($service['id']);
                    if ($activityResult['success'] && $activityResult['active']) {
                        $stats['active_services']++;
                    } else {
                        $stats['inactive_services']++;
                    }
                }
            }

            // Calcola intervallo medio
            if (count($this->heartbeats) > 1) {
                $intervals = [];
                for ($i = 1; $i < count($this->heartbeats); $i++) {
                    $prev = \Carbon\Carbon::parse($this->heartbeats[$i-1]['timestamp']);
                    $curr = \Carbon\Carbon::parse($this->heartbeats[$i]['timestamp']);
                    $intervals[] = $curr->diffInSeconds($prev);
                }
                $stats['average_heartbeat_interval'] = array_sum($intervals) / count($intervals);
            }

            // Top servizi per numero di heartbeat
            $serviceCounts = [];
            foreach ($this->heartbeats as $heartbeat) {
                $serviceId = $heartbeat['service_id'];
                $serviceCounts[$serviceId] = ($serviceCounts[$serviceId] ?? 0) + 1;
            }
            arsort($serviceCounts);
            $stats['top_services'] = array_slice($serviceCounts, 0, 10, true);

            // Trend degli ultimi 24 ore
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i);
                $hourStart = $hour->copy()->startOfHour();
                $hourEnd = $hour->copy()->endOfHour();
                
                $count = 0;
                foreach ($this->heartbeats as $heartbeat) {
                    $heartbeatTime = \Carbon\Carbon::parse($heartbeat['timestamp']);
                    if ($heartbeatTime->between($hourStart, $hourEnd)) {
                        $count++;
                    }
                }
                
                $stats['heartbeat_trend'][] = [
                    'hour' => $hour->format('H:00'),
                    'count' => $count
                ];
            }

            return [
                'success' => true,
                'data' => $stats,
                'heartbeat_service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nel recupero statistiche heartbeat", [
                'error' => $e->getMessage(),
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nel recupero delle statistiche degli heartbeat'
            ];
        }
    }

    /**
     * Configura il heartbeat per un servizio
     */
    public function configureHeartbeat(string $serviceId, array $config): array
    {
        try {
            $configKey = "heartbeat_config:{$serviceId}";
            $heartbeatConfig = [
                'interval_seconds' => $config['interval_seconds'] ?? 30,
                'timeout_minutes' => $config['timeout_minutes'] ?? 5,
                'retry_attempts' => $config['retry_attempts'] ?? 3,
                'enabled' => $config['enabled'] ?? true,
                'configured_at' => now()->toISOString()
            ];

            Cache::put($configKey, $heartbeatConfig, 86400); // 24 ore

            return [
                'success' => true,
                'message' => 'Heartbeat configurato con successo',
                'service_id' => $serviceId,
                'config' => $heartbeatConfig
            ];

        } catch (\Exception $e) {
            Log::error("Heartbeat Service: Errore nella configurazione heartbeat", [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'heartbeat_service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Errore nella configurazione del heartbeat'
            ];
        }
    }

    /**
     * Ottiene la configurazione del heartbeat per un servizio
     */
    public function getHeartbeatConfig(string $serviceId): array
    {
        try {
            $configKey = "heartbeat_config:{$serviceId}";
            $config = Cache::get($configKey);

            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Configurazione non trovata',
                    'service_id' => $serviceId
                ];
            }

            return [
                'success' => true,
                'config' => $config,
                'service_id' => $serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service_id' => $serviceId
            ];
        }
    }

    /**
     * Health check del servizio heartbeat
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'heartbeat_service' => $this->serviceId,
                'version' => $this->version,
                'total_heartbeats' => count($this->heartbeats),
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'heartbeat_service' => $this->serviceId,
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
