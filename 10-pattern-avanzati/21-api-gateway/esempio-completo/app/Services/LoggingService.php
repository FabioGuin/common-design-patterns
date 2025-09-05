<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoggingService
{
    protected $serviceId = 'logging-service';
    protected $version = '1.0.0';

    /**
     * Logga una richiesta
     */
    public function logRequest(Request $request, string $requestId): void
    {
        try {
            $logData = [
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'query' => $request->query->all(),
                'body' => $this->sanitizeBody($request->all()),
                'user_id' => $request->user()['id'] ?? null,
                'timestamp' => now()->toISOString(),
                'service' => $this->serviceId
            ];

            // Log strutturato
            Log::info('API Gateway Request', $logData);

            // Cache per analytics
            $this->cacheRequestData($logData);

        } catch (\Exception $e) {
            Log::error("Logging Service: Errore nel logging richiesta", [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'service' => $this->serviceId
            ]);
        }
    }

    /**
     * Logga una risposta
     */
    public function logResponse(Request $request, array $response, string $requestId): void
    {
        try {
            $logData = [
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response['status'] ?? 200,
                'success' => $response['success'] ?? false,
                'response_time' => $response['response_time'] ?? null,
                'cached' => $response['cached'] ?? false,
                'user_id' => $request->user()['id'] ?? null,
                'timestamp' => now()->toISOString(),
                'service' => $this->serviceId
            ];

            // Log strutturato
            if ($response['success'] ?? false) {
                Log::info('API Gateway Response', $logData);
            } else {
                Log::warning('API Gateway Error Response', $logData);
            }

            // Cache per analytics
            $this->cacheResponseData($logData);

        } catch (\Exception $e) {
            Log::error("Logging Service: Errore nel logging risposta", [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'service' => $this->serviceId
            ]);
        }
    }

    /**
     * Logga un errore
     */
    public function logError(\Exception $error, Request $request, string $requestId): void
    {
        try {
            $logData = [
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'error_message' => $error->getMessage(),
                'error_code' => $error->getCode(),
                'error_file' => $error->getFile(),
                'error_line' => $error->getLine(),
                'stack_trace' => $error->getTraceAsString(),
                'user_id' => $request->user()['id'] ?? null,
                'timestamp' => now()->toISOString(),
                'service' => $this->serviceId
            ];

            Log::error('API Gateway Error', $logData);

            // Cache per analytics
            $this->cacheErrorData($logData);

        } catch (\Exception $e) {
            Log::error("Logging Service: Errore nel logging errore", [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'service' => $this->serviceId
            ]);
        }
    }

    /**
     * Sanitizza gli header per il logging
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'x-api-key',
            'cookie',
            'x-csrf-token'
        ];

        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizza il body per il logging
     */
    private function sanitizeBody(array $body): array
    {
        $sensitiveFields = [
            'password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn'
        ];

        $sanitized = [];
        foreach ($body as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveFields)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeBody($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Cache i dati della richiesta per analytics
     */
    private function cacheRequestData(array $logData): void
    {
        try {
            $key = 'api_requests:' . now()->format('Y-m-d-H');
            $requests = Cache::get($key, []);
            $requests[] = $logData;
            
            // Mantieni solo gli ultimi 1000 record per ora
            if (count($requests) > 1000) {
                $requests = array_slice($requests, -1000);
            }
            
            Cache::put($key, $requests, 3600); // 1 ora

        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Cache i dati della risposta per analytics
     */
    private function cacheResponseData(array $logData): void
    {
        try {
            $key = 'api_responses:' . now()->format('Y-m-d-H');
            $responses = Cache::get($key, []);
            $responses[] = $logData;
            
            // Mantieni solo gli ultimi 1000 record per ora
            if (count($responses) > 1000) {
                $responses = array_slice($responses, -1000);
            }
            
            Cache::put($key, $responses, 3600); // 1 ora

        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Cache i dati dell'errore per analytics
     */
    private function cacheErrorData(array $logData): void
    {
        try {
            $key = 'api_errors:' . now()->format('Y-m-d-H');
            $errors = Cache::get($key, []);
            $errors[] = $logData;
            
            // Mantieni solo gli ultimi 100 record per ora
            if (count($errors) > 100) {
                $errors = array_slice($errors, -100);
            }
            
            Cache::put($key, $errors, 3600); // 1 ora

        } catch (\Exception $e) {
            // Ignora errori di cache
        }
    }

    /**
     * Ottiene le statistiche di logging
     */
    public function getLoggingStats(): array
    {
        try {
            $stats = [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'errors' => 0,
                'average_response_time' => 0,
                'top_endpoints' => [],
                'top_users' => [],
                'error_types' => []
            ];

            // Analizza i dati delle ultime 24 ore
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                
                $requests = Cache::get('api_requests:' . $hour, []);
                $responses = Cache::get('api_responses:' . $hour, []);
                $errors = Cache::get('api_errors:' . $hour, []);
                
                $stats['total_requests'] += count($requests);
                $stats['successful_requests'] += count(array_filter($responses, fn($r) => $r['success'] ?? false));
                $stats['failed_requests'] += count(array_filter($responses, fn($r) => !($r['success'] ?? false)));
                $stats['errors'] += count($errors);
            }

            // Calcola tempo di risposta medio
            $responseTimes = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $responses = Cache::get('api_responses:' . $hour, []);
                
                foreach ($responses as $response) {
                    if (isset($response['response_time'])) {
                        $responseTimes[] = $response['response_time'];
                    }
                }
            }
            
            if (!empty($responseTimes)) {
                $stats['average_response_time'] = array_sum($responseTimes) / count($responseTimes);
            }

            // Top endpoints
            $endpointCounts = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $requests = Cache::get('api_requests:' . $hour, []);
                
                foreach ($requests as $request) {
                    $endpoint = $request['path'] ?? 'unknown';
                    $endpointCounts[$endpoint] = ($endpointCounts[$endpoint] ?? 0) + 1;
                }
            }
            
            arsort($endpointCounts);
            $stats['top_endpoints'] = array_slice($endpointCounts, 0, 10, true);

            // Top users
            $userCounts = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $requests = Cache::get('api_requests:' . $hour, []);
                
                foreach ($requests as $request) {
                    $userId = $request['user_id'] ?? 'anonymous';
                    $userCounts[$userId] = ($userCounts[$userId] ?? 0) + 1;
                }
            }
            
            arsort($userCounts);
            $stats['top_users'] = array_slice($userCounts, 0, 10, true);

            // Error types
            $errorTypes = [];
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $errors = Cache::get('api_errors:' . $hour, []);
                
                foreach ($errors as $error) {
                    $errorType = $error['error_code'] ?? 'unknown';
                    $errorTypes[$errorType] = ($errorTypes[$errorType] ?? 0) + 1;
                }
            }
            
            arsort($errorTypes);
            $stats['error_types'] = array_slice($errorTypes, 0, 10, true);

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
     * Ottiene i log per un periodo specifico
     */
    public function getLogs(string $startDate, string $endDate, int $limit = 100): array
    {
        try {
            $logs = [];
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            
            $current = $start->copy();
            while ($current->lte($end)) {
                $hour = $current->format('Y-m-d-H');
                
                $requests = Cache::get('api_requests:' . $hour, []);
                $responses = Cache::get('api_responses:' . $hour, []);
                $errors = Cache::get('api_errors:' . $hour, []);
                
                $logs = array_merge($logs, $requests, $responses, $errors);
                
                $current->addHour();
            }
            
            // Ordina per timestamp
            usort($logs, function($a, $b) {
                return strcmp($a['timestamp'] ?? '', $b['timestamp'] ?? '');
            });
            
            // Limita i risultati
            $logs = array_slice($logs, -$limit);
            
            return [
                'success' => true,
                'data' => $logs,
                'count' => count($logs),
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
     * Pulisce i log vecchi
     */
    public function cleanOldLogs(int $days = 7): array
    {
        try {
            $cleaned = 0;
            $cutoff = now()->subDays($days);
            
            for ($i = 0; $i < 24 * $days; $i++) {
                $hour = $cutoff->subHours($i)->format('Y-m-d-H');
                
                $keys = [
                    'api_requests:' . $hour,
                    'api_responses:' . $hour,
                    'api_errors:' . $hour
                ];
                
                foreach ($keys as $key) {
                    if (Cache::has($key)) {
                        Cache::forget($key);
                        $cleaned++;
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Cleaned {$cleaned} log entries older than {$days} days",
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
