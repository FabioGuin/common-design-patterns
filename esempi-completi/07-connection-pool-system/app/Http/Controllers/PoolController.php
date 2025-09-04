<?php

namespace App\Http\Controllers;

use App\Services\ConnectionPool;
use App\Services\ResourcePool;
use App\Services\PoolManager;
use App\Models\FileConnection;
use App\Models\CacheConnection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PoolController extends Controller
{
    private PoolManager $poolManager;

    public function __construct(PoolManager $poolManager)
    {
        $this->poolManager = $poolManager;
        
        // Inizializza i pool se non esistono
        $this->initializePools();
    }

    private function initializePools(): void
    {
        if (!$this->poolManager->hasPool('database')) {
            $this->poolManager->addPool('database', new ConnectionPool('mysql', 10));
        }
        
        if (!$this->poolManager->hasPool('files')) {
            $this->poolManager->addPool('files', new ResourcePool(FileConnection::class, 5, ['/tmp/test.txt', 'w']));
        }
        
        if (!$this->poolManager->hasPool('cache')) {
            $this->poolManager->addPool('cache', new ResourcePool(CacheConnection::class, 8, ['default']));
        }
    }

    public function index(): JsonResponse
    {
        $pools = $this->poolManager->getAllStats();
        
        return response()->json([
            'success' => true,
            'data' => [
                'pools' => $pools,
                'summary' => [
                    'total_pools' => $this->poolManager->getTotalPools(),
                    'total_resources' => $this->poolManager->getTotalResources(),
                    'total_in_use' => $this->poolManager->getTotalInUse(),
                    'total_available' => $this->poolManager->getTotalAvailable(),
                ]
            ]
        ]);
    }

    public function getStats(string $poolName): JsonResponse
    {
        try {
            $stats = $this->poolManager->getPoolStats($poolName);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero delle statistiche',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function acquire(string $poolName, Request $request): JsonResponse
    {
        try {
            $acquiredBy = $request->input('acquired_by', 'api_request');
            $resource = $this->poolManager->acquire($poolName, $acquiredBy);
            
            return response()->json([
                'success' => true,
                'message' => 'Risorsa acquisita con successo',
                'data' => [
                    'pool_name' => $poolName,
                    'acquired_by' => $acquiredBy,
                    'resource_id' => spl_object_id($resource),
                    'acquired_at' => $resource->getAcquiredAt(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'acquisizione della risorsa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function release(string $poolName, Request $request): JsonResponse
    {
        try {
            $resourceId = $request->input('resource_id');
            
            if (!$resourceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID risorsa richiesto'
                ], 400);
            }
            
            // In un'implementazione reale, dovresti mantenere un registro delle risorse
            // Per questo esempio, assumiamo che la risorsa sia valida
            $this->poolManager->release($poolName, (object)['id' => $resourceId]);
            
            return response()->json([
                'success' => true,
                'message' => 'Risorsa rilasciata con successo',
                'data' => [
                    'pool_name' => $poolName,
                    'resource_id' => $resourceId,
                    'released_at' => time()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il rilascio della risorsa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function healthCheck(string $poolName = null): JsonResponse
    {
        try {
            $health = $this->poolManager->healthCheck($poolName);
            
            return response()->json([
                'success' => true,
                'data' => $health
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il controllo della salute',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cleanup(string $poolName = null): JsonResponse
    {
        try {
            $results = $this->poolManager->cleanup($poolName);
            
            return response()->json([
                'success' => true,
                'message' => 'Cleanup completato con successo',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il cleanup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reset(string $poolName = null): JsonResponse
    {
        try {
            $this->poolManager->reset($poolName);
            
            return response()->json([
                'success' => true,
                'message' => 'Reset completato con successo',
                'data' => [
                    'pool_name' => $poolName,
                    'reset_at' => time()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il reset',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testDatabasePool(): JsonResponse
    {
        try {
            $connection = $this->poolManager->acquire('database', 'test_request');
            
            // Esegui una query di test
            $result = $connection->query('SELECT 1 as test');
            
            $this->poolManager->release('database', $connection);
            
            return response()->json([
                'success' => true,
                'message' => 'Test database pool completato con successo',
                'data' => [
                    'query_result' => $result,
                    'connection_used' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test del database pool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testFilePool(): JsonResponse
    {
        try {
            $file = $this->poolManager->acquire('files', 'test_request');
            
            // Test operazioni file
            $testData = 'Test data: ' . date('Y-m-d H:i:s');
            $bytesWritten = $file->write($testData);
            
            $this->poolManager->release('files', $file);
            
            return response()->json([
                'success' => true,
                'message' => 'Test file pool completato con successo',
                'data' => [
                    'bytes_written' => $bytesWritten,
                    'test_data' => $testData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test del file pool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testCachePool(): JsonResponse
    {
        try {
            $cache = $this->poolManager->acquire('cache', 'test_request');
            
            // Test operazioni cache
            $testKey = 'test_key_' . time();
            $testValue = 'test_value_' . date('Y-m-d H:i:s');
            
            $cache->set($testKey, $testValue, 60);
            $retrievedValue = $cache->get($testKey);
            
            $this->poolManager->release('cache', $cache);
            
            return response()->json([
                'success' => true,
                'message' => 'Test cache pool completato con successo',
                'data' => [
                    'test_key' => $testKey,
                    'test_value' => $testValue,
                    'retrieved_value' => $retrievedValue,
                    'values_match' => $testValue === $retrievedValue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test del cache pool',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stressTest(string $poolName, Request $request): JsonResponse
    {
        try {
            $iterations = $request->input('iterations', 10);
            $concurrent = $request->input('concurrent', 5);
            
            $results = [
                'pool_name' => $poolName,
                'iterations' => $iterations,
                'concurrent' => $concurrent,
                'start_time' => time(),
                'successful' => 0,
                'failed' => 0,
                'errors' => []
            ];
            
            for ($i = 0; $i < $iterations; $i++) {
                $resources = [];
                
                // Acquisisci risorse concorrenti
                for ($j = 0; $j < $concurrent; $j++) {
                    try {
                        $resource = $this->poolManager->acquire($poolName, "stress_test_{$i}_{$j}");
                        $resources[] = $resource;
                        $results['successful']++;
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $results['errors'][] = $e->getMessage();
                    }
                }
                
                // Rilascia le risorse
                foreach ($resources as $resource) {
                    try {
                        $this->poolManager->release($poolName, $resource);
                    } catch (\Exception $e) {
                        $results['errors'][] = $e->getMessage();
                    }
                }
            }
            
            $results['end_time'] = time();
            $results['duration'] = $results['end_time'] - $results['start_time'];
            
            return response()->json([
                'success' => true,
                'message' => 'Stress test completato',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante lo stress test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
