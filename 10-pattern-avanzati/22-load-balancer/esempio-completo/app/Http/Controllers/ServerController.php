<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{
    private array $serverStats = [];
    private bool $isHealthy = true;

    public function __construct()
    {
        $this->initializeStats();
    }

    /**
     * Gestisce le richieste al server
     */
    public function handle(Request $request, string $id): JsonResponse
    {
        $this->updateStats($id, 'request');
        
        // Simula un tempo di elaborazione variabile
        $processingTime = mt_rand(50, 200); // 50-200ms
        usleep($processingTime * 1000);

        $response = [
            'server_id' => $id,
            'message' => 'Risposta dal server ' . $id,
            'timestamp' => now()->toISOString(),
            'processing_time_ms' => $processingTime,
            'request_data' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl()
            ]
        ];

        Log::info("Richiesta gestita dal server", [
            'server_id' => $id,
            'processing_time' => $processingTime
        ]);

        return response()->json($response);
    }

    /**
     * Endpoint di health check
     */
    public function health(string $id): JsonResponse
    {
        $this->updateStats($id, 'health_check');

        $response = [
            'server_id' => $id,
            'status' => $this->isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage()
        ];

        return response()->json($response);
    }

    /**
     * Ottiene le statistiche del server
     */
    public function stats(string $id): JsonResponse
    {
        $stats = $this->serverStats[$id] ?? $this->initializeServerStats($id);

        return response()->json([
            'server_id' => $id,
            'stats' => $stats,
            'is_healthy' => $this->isHealthy,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Simula un server down
     */
    public function simulateDown(string $id): JsonResponse
    {
        $this->isHealthy = false;
        $this->updateStats($id, 'simulated_down');

        Log::warning("Server simulato come down", ['server_id' => $id]);

        return response()->json([
            'server_id' => $id,
            'status' => 'simulated_down',
            'message' => 'Server simulato come non disponibile',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Ripristina un server
     */
    public function restore(string $id): JsonResponse
    {
        $this->isHealthy = true;
        $this->updateStats($id, 'restored');

        Log::info("Server ripristinato", ['server_id' => $id]);

        return response()->json([
            'server_id' => $id,
            'status' => 'restored',
            'message' => 'Server ripristinato',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Simula un errore
     */
    public function simulateError(string $id): JsonResponse
    {
        $this->updateStats($id, 'error');

        Log::error("Errore simulato dal server", ['server_id' => $id]);

        return response()->json([
            'server_id' => $id,
            'status' => 'error',
            'message' => 'Errore simulato',
            'error_code' => 'SIMULATED_ERROR',
            'timestamp' => now()->toISOString()
        ], 500);
    }

    /**
     * Inizializza le statistiche
     */
    private function initializeStats(): void
    {
        $this->serverStats = [];
    }

    /**
     * Inizializza le statistiche per un server
     */
    private function initializeServerStats(string $id): array
    {
        $this->serverStats[$id] = [
            'requests' => 0,
            'health_checks' => 0,
            'errors' => 0,
            'total_processing_time' => 0,
            'avg_processing_time' => 0,
            'last_request' => null,
            'start_time' => now()->toISOString()
        ];

        return $this->serverStats[$id];
    }

    /**
     * Aggiorna le statistiche del server
     */
    private function updateStats(string $id, string $type): void
    {
        if (!isset($this->serverStats[$id])) {
            $this->initializeServerStats($id);
        }

        $stats = &$this->serverStats[$id];

        switch ($type) {
            case 'request':
                $stats['requests']++;
                $stats['last_request'] = now()->toISOString();
                break;
            case 'health_check':
                $stats['health_checks']++;
                break;
            case 'error':
                $stats['errors']++;
                break;
            case 'simulated_down':
                $stats['simulated_down'] = now()->toISOString();
                break;
            case 'restored':
                unset($stats['simulated_down']);
                break;
        }
    }

    /**
     * Ottiene l'uptime del server
     */
    private function getUptime(): string
    {
        $startTime = now()->subMinutes(mt_rand(60, 1440)); // 1-24 ore
        return $startTime->diffForHumans();
    }

    /**
     * Ottiene l'uso della memoria
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'current_bytes' => $memoryUsage,
            'peak_bytes' => $memoryPeak
        ];
    }

    /**
     * Ottiene l'uso della CPU
     */
    private function getCpuUsage(): array
    {
        // Simula l'uso della CPU
        $usage = mt_rand(10, 80);
        
        return [
            'percentage' => $usage,
            'load_average' => [
                '1min' => round($usage / 100, 2),
                '5min' => round($usage / 100 * 0.8, 2),
                '15min' => round($usage / 100 * 0.6, 2)
            ]
        ];
    }

    /**
     * Formatta i byte in unità leggibili
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
