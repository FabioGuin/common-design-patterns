<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class LogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Log inizio richiesta
        $this->logRequestStart($request);

        // Esegui la richiesta
        $response = $next($request);

        // Calcola metriche
        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;
        $peakMemory = memory_get_peak_usage();

        // Log fine richiesta
        $this->logRequestEnd($request, $response, $duration, $memoryUsed, $peakMemory);

        return $response;
    }

    /**
     * Log inizio richiesta
     */
    protected function logRequestStart(Request $request): void
    {
        $logData = [
            'type' => 'request_start',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ];

        // Aggiungi parametri se non sono sensibili
        if ($this->shouldLogParameters($request)) {
            $logData['parameters'] = $request->all();
        }

        Log::info('HTTP Request Started', $logData);
    }

    /**
     * Log fine richiesta
     */
    protected function logRequestEnd(Request $request, $response, float $duration, int $memoryUsed, int $peakMemory): void
    {
        $logData = [
            'type' => 'request_end',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ];

        // Aggiungi informazioni sulla risposta
        if ($response->headers->has('Content-Type')) {
            $logData['content_type'] = $response->headers->get('Content-Type');
        }

        if ($response->headers->has('Content-Length')) {
            $logData['content_length'] = $response->headers->get('Content-Length');
        }

        // Log livello basato sullo status code
        $logLevel = $this->getLogLevel($response->getStatusCode());
        Log::log($logLevel, 'HTTP Request Completed', $logData);

        // Log warning per richieste lente
        if ($duration > Config::get('logging.slow_request_threshold', 1.0)) {
            Log::warning('Slow Request Detected', array_merge($logData, [
                'threshold' => Config::get('logging.slow_request_threshold', 1.0),
            ]));
        }

        // Log error per richieste con errori
        if ($response->getStatusCode() >= 400) {
            Log::error('HTTP Error Response', array_merge($logData, [
                'error_details' => $this->getErrorDetails($response),
            ]));
        }
    }

    /**
     * Determina se loggare i parametri della richiesta
     */
    protected function shouldLogParameters(Request $request): bool
    {
        // Non loggare parametri per richieste sensibili
        $sensitivePaths = [
            'password',
            'token',
            'secret',
            'key',
        ];

        $allParameters = $request->all();
        foreach ($allParameters as $key => $value) {
            if (in_array(strtolower($key), $sensitivePaths)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determina il livello di log basato sullo status code
     */
    protected function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        } elseif ($statusCode >= 400) {
            return 'warning';
        } elseif ($statusCode >= 300) {
            return 'info';
        } else {
            return 'info';
        }
    }

    /**
     * Ottieni dettagli dell'errore dalla risposta
     */
    protected function getErrorDetails($response): array
    {
        $details = [];

        if (method_exists($response, 'getData')) {
            $data = $response->getData(true);
            if (isset($data['message'])) {
                $details['message'] = $data['message'];
            }
            if (isset($data['errors'])) {
                $details['errors'] = $data['errors'];
            }
        }

        return $details;
    }
}
