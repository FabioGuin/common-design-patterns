<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonitoringService
{
    protected $serviceId = 'monitoring-service';
    protected $version = '1.0.0';

    /**
     * Registra le metriche per una richiesta
     */
    public function recordMetrics(Request $request, array $response, float $responseTime): void
    {
        try {
            $metrics = [
                'timestamp' => now()->toISOString(),
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response['status'] ?? 200,
                'success' => $response['success'] ?? false,
                'response_time' => $responseTime,
                'user_id' => $request->user()['id'] ?? null,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'service' => $this->serviceId
            ];

            // Cache le metriche per analytics
            $this->cacheMetrics($metrics);

            // Log per monitoring
            Log::info('API Gateway Metrics', $metrics);

        } catch (\Exception $e) {
            Log::error("Monitoring Service: Errore nel recording metriche", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);
        }
    }

    /**
     * Cache le metriche per analytics
     */
    private function cacheMetrics(array $metrics): void
    {
        try {
            $key = 'api_metrics:' . now()->format('Y-m-d-H');
            $existingMetrics = Cache::get($key, []);
            $existingMetrics[] = $metrics;
            
            // Mantieni solo gli ultimi 1000 record per ora
            if (count($existingMetrics) > 1000) {
                $existingMetrics = array_slice($existingMetrics, -1000);
            }
            
            Cache::put($key, $existingMetrics, 3600); // 1 ora

        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Ottiene le metriche per un periodo specifico
     */
    public function getMetrics(string $startDate, string $endDate): array
    {
        try {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            
            $metrics = [];
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $hour = $current->format('Y-m-d-H');
                $hourMetrics = Cache::get('api_metrics:' . $hour, []);
                $metrics = array_merge($metrics, $hourMetrics);
                $current->addHour();
            }
            
            return [
                'success' => true,
                'data' => $metrics,
                'count' => count($metrics),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public function getAggregatedStats(): array
    {
        try {
            $stats = [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'average_response_time' => 0,
                'p95_response_time' => 0,
                'p99_response_time' => 0,
                'requests_per_minute' => 0,
                'error_rate' => 0,
                'top_endpoints' => [],
                'top_users' => [],
                'status_codes' => [],
                'response_times' => []
            ];

            // Analizza le metriche delle ultime 24 ore
            $allMetrics = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $hourMetrics = Cache::get('api_metrics:' . $hour, []);
                $allMetrics = array_merge($allMetrics, $hourMetrics);
            }

            if (empty($allMetrics)) {
                return [
                    'success' => true,
                    'data' => $stats,
                    'service' => $this->serviceId
                ];
            }

            // Calcola statistiche
            $stats['total_requests'] = count($allMetrics);
            $stats['successful_requests'] = count(array_filter($allMetrics, fn($m) => $m['success'] ?? false));
            $stats['failed_requests'] = $stats['total_requests'] - $stats['successful_requests'];
            $stats['error_rate'] = $stats['total_requests'] > 0 ? ($stats['failed_requests'] / $stats['total_requests']) * 100 : 0;

            // Calcola tempi di risposta
            $responseTimes = array_column($allMetrics, 'response_time');
            $responseTimes = array_filter($responseTimes, fn($rt) => $rt !== null);
            
            if (!empty($responseTimes)) {
                sort($responseTimes);
                $stats['average_response_time'] = array_sum($responseTimes) / count($responseTimes);
                $stats['p95_response_time'] = $this->calculatePercentile($responseTimes, 95);
                $stats['p99_response_time'] = $this->calculatePercentile($responseTimes, 99);
            }

            // Calcola richieste per minuto
            $stats['requests_per_minute'] = $stats['total_requests'] / (24 * 60);

            // Top endpoints
            $endpointCounts = [];
            foreach ($allMetrics as $metric) {
                $endpoint = $metric['path'] ?? 'unknown';
                $endpointCounts[$endpoint] = ($endpointCounts[$endpoint] ?? 0) + 1;
            }
            arsort($endpointCounts);
            $stats['top_endpoints'] = array_slice($endpointCounts, 0, 10, true);

            // Top users
            $userCounts = [];
            foreach ($allMetrics as $metric) {
                $userId = $metric['user_id'] ?? 'anonymous';
                $userCounts[$userId] = ($userCounts[$userId] ?? 0) + 1;
            }
            arsort($userCounts);
            $stats['top_users'] = array_slice($userCounts, 0, 10, true);

            // Status codes
            $statusCounts = [];
            foreach ($allMetrics as $metric) {
                $status = $metric['status_code'] ?? 200;
                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            }
            arsort($statusCounts);
            $stats['status_codes'] = $statusCounts;

            // Response times per endpoint
            $endpointResponseTimes = [];
            foreach ($allMetrics as $metric) {
                $endpoint = $metric['path'] ?? 'unknown';
                $responseTime = $metric['response_time'] ?? 0;
                if (!isset($endpointResponseTimes[$endpoint])) {
                    $endpointResponseTimes[$endpoint] = [];
                }
                $endpointResponseTimes[$endpoint][] = $responseTime;
            }

            foreach ($endpointResponseTimes as $endpoint => $times) {
                if (!empty($times)) {
                    $stats['response_times'][$endpoint] = [
                        'average' => array_sum($times) / count($times),
                        'min' => min($times),
                        'max' => max($times),
                        'count' => count($times)
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $stats,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Calcola un percentile
     */
    private function calculatePercentile(array $values, float $percentile): float
    {
        $index = ($percentile / 100) * (count($values) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower === $upper) {
            return $values[$lower];
        }
        
        $weight = $index - $lower;
        return $values[$lower] * (1 - $weight) + $values[$upper] * $weight;
    }

    /**
     * Ottiene le metriche in tempo reale
     */
    public function getRealTimeMetrics(): array
    {
        try {
            $currentHour = now()->format('Y-m-d-H');
            $metrics = Cache::get('api_metrics:' . $currentHour, []);

            $realTimeStats = [
                'current_hour' => $currentHour,
                'requests_this_hour' => count($metrics),
                'successful_requests' => count(array_filter($metrics, fn($m) => $m['success'] ?? false)),
                'failed_requests' => count(array_filter($metrics, fn($m) => !($m['success'] ?? false))),
                'average_response_time' => 0,
                'last_request' => null
            ];

            if (!empty($metrics)) {
                $responseTimes = array_column($metrics, 'response_time');
                $responseTimes = array_filter($responseTimes, fn($rt) => $rt !== null);
                
                if (!empty($responseTimes)) {
                    $realTimeStats['average_response_time'] = array_sum($responseTimes) / count($responseTimes);
                }

                $realTimeStats['last_request'] = end($metrics);
            }

            return [
                'success' => true,
                'data' => $realTimeStats,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene le metriche per endpoint
     */
    public function getEndpointMetrics(string $endpoint): array
    {
        try {
            $allMetrics = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $hourMetrics = Cache::get('api_metrics:' . $hour, []);
                $allMetrics = array_merge($allMetrics, $hourMetrics);
            }

            $endpointMetrics = array_filter($allMetrics, fn($m) => $m['path'] === $endpoint);

            if (empty($endpointMetrics)) {
                return [
                    'success' => false,
                    'error' => 'No metrics found for endpoint',
                    'endpoint' => $endpoint
                ];
            }

            $responseTimes = array_column($endpointMetrics, 'response_time');
            $responseTimes = array_filter($responseTimes, fn($rt) => $rt !== null);

            $stats = [
                'endpoint' => $endpoint,
                'total_requests' => count($endpointMetrics),
                'successful_requests' => count(array_filter($endpointMetrics, fn($m) => $m['success'] ?? false)),
                'failed_requests' => count(array_filter($endpointMetrics, fn($m) => !($m['success'] ?? false))),
                'average_response_time' => 0,
                'min_response_time' => 0,
                'max_response_time' => 0,
                'p95_response_time' => 0,
                'p99_response_time' => 0
            ];

            if (!empty($responseTimes)) {
                sort($responseTimes);
                $stats['average_response_time'] = array_sum($responseTimes) / count($responseTimes);
                $stats['min_response_time'] = min($responseTimes);
                $stats['max_response_time'] = max($responseTimes);
                $stats['p95_response_time'] = $this->calculatePercentile($responseTimes, 95);
                $stats['p99_response_time'] = $this->calculatePercentile($responseTimes, 99);
            }

            return [
                'success' => true,
                'data' => $stats,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene le metriche per utente
     */
    public function getUserMetrics(string $userId): array
    {
        try {
            $allMetrics = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $hourMetrics = Cache::get('api_metrics:' . $hour, []);
                $allMetrics = array_merge($allMetrics, $hourMetrics);
            }

            $userMetrics = array_filter($allMetrics, fn($m) => $m['user_id'] === $userId);

            if (empty($userMetrics)) {
                return [
                    'success' => false,
                    'error' => 'No metrics found for user',
                    'user_id' => $userId
                ];
            }

            $responseTimes = array_column($userMetrics, 'response_time');
            $responseTimes = array_filter($responseTimes, fn($rt) => $rt !== null);

            $stats = [
                'user_id' => $userId,
                'total_requests' => count($userMetrics),
                'successful_requests' => count(array_filter($userMetrics, fn($m) => $m['success'] ?? false)),
                'failed_requests' => count(array_filter($userMetrics, fn($m) => !($m['success'] ?? false))),
                'average_response_time' => 0,
                'top_endpoints' => []
            ];

            if (!empty($responseTimes)) {
                $stats['average_response_time'] = array_sum($responseTimes) / count($responseTimes);
            }

            // Top endpoints per utente
            $endpointCounts = [];
            foreach ($userMetrics as $metric) {
                $endpoint = $metric['path'] ?? 'unknown';
                $endpointCounts[$endpoint] = ($endpointCounts[$endpoint] ?? 0) + 1;
            }
            arsort($endpointCounts);
            $stats['top_endpoints'] = array_slice($endpointCounts, 0, 10, true);

            return [
                'success' => true,
                'data' => $stats,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Pulisce le metriche vecchie
     */
    public function cleanOldMetrics(int $days = 7): array
    {
        try {
            $cleaned = 0;
            $cutoff = now()->subDays($days);
            
            for ($i = 0; $i < 24 * $days; $i++) {
                $hour = $cutoff->subHours($i)->format('Y-m-d-H');
                $key = 'api_metrics:' . $hour;
                
                if (Cache::has($key)) {
                    Cache::forget($key);
                    $cleaned++;
                }
            }
            
            return [
                'success' => true,
                'message' => "Cleaned {$cleaned} metric entries older than {$days} days",
                'cleaned' => $cleaned,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
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
