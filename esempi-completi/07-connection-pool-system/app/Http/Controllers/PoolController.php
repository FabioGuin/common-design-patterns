<?php

namespace App\Http\Controllers;

use App\Services\PoolManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PoolController extends Controller
{
    private PoolManager $poolManager;

    public function __construct()
    {
        $this->poolManager = PoolManager::getInstance();
    }

    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->poolManager->getAllStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero statistiche pool: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore interno del server',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPoolStats(string $poolName): JsonResponse
    {
        try {
            $pool = $this->poolManager->getPool($poolName);
            $stats = $pool->getStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'pool_name' => $poolName,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero statistiche pool {$poolName}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Pool non trovato',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getHealth(): JsonResponse
    {
        try {
            $health = $this->poolManager->getGlobalHealthStatus();
            
            $statusCode = 200;
            if ($health['status'] === 'warning') {
                $statusCode = 200; // Warning non è un errore
            } elseif ($health['status'] === 'critical') {
                $statusCode = 503; // Service Unavailable
            }
            
            return response()->json([
                'success' => true,
                'data' => $health
            ], $statusCode);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero stato salute pool: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore interno del server',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPoolHealth(string $poolName): JsonResponse
    {
        try {
            $pool = $this->poolManager->getPool($poolName);
            $health = $pool->getHealthStatus();
            
            $statusCode = 200;
            if ($health['status'] === 'warning') {
                $statusCode = 200;
            } elseif ($health['status'] === 'critical') {
                $statusCode = 503;
            }
            
            return response()->json([
                'success' => true,
                'data' => $health,
                'pool_name' => $poolName
            ], $statusCode);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero stato salute pool {$poolName}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Pool non trovato',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function resetPool(string $poolName): JsonResponse
    {
        try {
            $pool = $this->poolManager->getPool($poolName);
            $pool->reset();
            
            Log::info("Pool {$poolName} resettato via API");
            
            return response()->json([
                'success' => true,
                'message' => "Pool '{$poolName}' resettato con successo",
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore reset pool {$poolName}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore durante il reset',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetAllPools(): JsonResponse
    {
        try {
            $this->poolManager->resetAllPools();
            
            Log::info("Tutti i pool resettati via API");
            
            return response()->json([
                'success' => true,
                'message' => 'Tutti i pool sono stati resettati con successo',
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore reset tutti i pool: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore durante il reset',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createPool(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'connection_name' => 'required|string|max:255',
                'max_size' => 'integer|min:1|max:100',
                'timeout' => 'integer|min:1|max:300',
                'retry_attempts' => 'integer|min:1|max:10'
            ]);

            $pool = $this->poolManager->createPool(
                $request->input('name'),
                $request->input('connection_name'),
                $request->input('max_size', 10),
                $request->input('timeout', 30),
                $request->input('retry_attempts', 3)
            );

            return response()->json([
                'success' => true,
                'message' => "Pool '{$request->input('name')}' creato con successo",
                'data' => $pool->getStats(),
                'timestamp' => now()->toISOString()
            ], 201);
            
        } catch (\Exception $e) {
            Log::error("Errore creazione pool: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore durante la creazione',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getGlobalStats(): JsonResponse
    {
        try {
            $stats = $this->poolManager->getGlobalStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero statistiche globali: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore interno del server',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPoolList(): JsonResponse
    {
        try {
            $pools = $this->poolManager->getAllPools();
            $poolList = [];
            
            foreach ($pools as $name => $pool) {
                $poolList[] = [
                    'name' => $name,
                    'stats' => $pool->getStats()
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $poolList,
                'total_pools' => count($poolList)
            ]);
            
        } catch (\Exception $e) {
            Log::error("Errore recupero lista pool: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Errore interno del server',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}