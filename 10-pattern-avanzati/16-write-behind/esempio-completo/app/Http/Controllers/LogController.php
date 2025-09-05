<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\LogEntry;
use App\Services\WriteBehindService;

class LogController extends Controller
{
    protected $writeBehindService;

    public function __construct(WriteBehindService $writeBehindService)
    {
        $this->writeBehindService = $writeBehindService;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('write-behind.example');
    }

    /**
     * Test del pattern Write-Behind
     */
    public function test()
    {
        try {
            // Test con il service
            $serviceResults = $this->writeBehindService->testWriteBehind();
            
            // Test con il model
            $modelResults = LogEntry::testWriteBehind();
            
            return response()->json([
                'success' => true,
                'message' => 'Test Write-Behind completato',
                'service_test' => $serviceResults,
                'model_test' => $modelResults,
                'stats' => $this->writeBehindService->getStats(),
                'queue_info' => $this->writeBehindService->getQueueInfo()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuovo log con Write-Behind
     */
    public function store(Request $request)
    {
        $request->validate([
            'level' => 'required|string|in:debug,info,warning,error,critical',
            'message' => 'required|string|max:1000',
            'context' => 'sometimes|array',
            'user_id' => 'sometimes|integer',
            'ip_address' => 'sometimes|ip',
            'user_agent' => 'sometimes|string|max:500'
        ]);

        try {
            $log = LogEntry::create([
                'level' => $request->level,
                'message' => $request->message,
                'context' => $request->context ?? [],
                'user_id' => $request->user_id,
                'ip_address' => $request->ip_address ?? $request->ip(),
                'user_agent' => $request->user_agent ?? $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Log creato con Write-Behind',
                'data' => [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->message,
                    'created_at' => $log->created_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostra un log (lettura dalla cache)
     */
    public function show($id)
    {
        try {
            $log = LogEntry::findWithCache($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log non trovato'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $log
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella lettura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista tutti i log
     */
    public function list()
    {
        try {
            // Per semplicità, leggiamo dal database
            // In produzione, potresti voler implementare una cache per le liste
            $logs = LogEntry::orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella lettura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test di performance del pattern
     */
    public function performanceTest()
    {
        try {
            $iterations = 1000;
            $times = [];

            // Test scrittura
            $start = microtime(true);
            $logIds = [];
            for ($i = 0; $i < $iterations; $i++) {
                $log = LogEntry::create([
                    'level' => 'info',
                    'message' => "Performance test {$i}",
                    'context' => ['iteration' => $i, 'test' => true],
                    'user_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Performance Test Agent'
                ]);
                $logIds[] = $log->id;
            }
            $times['write'] = microtime(true) - $start;

            // Test lettura
            $start = microtime(true);
            for ($i = 0; $i < min(100, $iterations); $i++) {
                LogEntry::findWithCache($logIds[$i] ?? 'test');
            }
            $times['read'] = microtime(true) - $start;

            return response()->json([
                'success' => true,
                'message' => 'Test di performance completato',
                'iterations' => $iterations,
                'times' => $times,
                'avg_write_time' => $times['write'] / $iterations,
                'avg_read_time' => $times['read'] / min(100, $iterations),
                'writes_per_second' => $iterations / $times['write'],
                'reads_per_second' => min(100, $iterations) / $times['read']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel test di performance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche del sistema
     */
    public function stats()
    {
        try {
            $stats = [
                'pattern_stats' => $this->writeBehindService->getStats(),
                'queue_info' => $this->writeBehindService->getQueueInfo(),
                'database_stats' => [
                    'total_logs' => LogEntry::count(),
                    'recent_logs' => LogEntry::where('created_at', '>=', now()->subHour())->count(),
                    'logs_by_level' => LogEntry::selectRaw('level, count(*) as count')
                        ->groupBy('level')
                        ->get()
                        ->pluck('count', 'level')
                ],
                'cache_stats' => [
                    'status' => 'active',
                    'prefix' => 'write_behind',
                    'ttl' => 3600
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test di stress del pattern
     */
    public function stressTest()
    {
        try {
            $iterations = 5000;
            $start = microtime(true);
            
            // Crea un batch di log per test di stress
            $batchData = [];
            for ($i = 0; $i < $iterations; $i++) {
                $batchData[] = [
                    'level' => ['debug', 'info', 'warning', 'error'][rand(0, 3)],
                    'message' => "Stress test message {$i}",
                    'context' => ['stress_test' => true, 'iteration' => $i],
                    'user_id' => rand(1, 100),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Stress Test Agent'
                ];
            }
            
            // Scrittura in batch
            $results = $this->writeBehindService->writeBatch('log', $batchData);
            $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            
            $totalTime = microtime(true) - $start;
            
            return response()->json([
                'success' => true,
                'message' => 'Test di stress completato',
                'iterations' => $iterations,
                'successful_writes' => $successCount,
                'failed_writes' => $iterations - $successCount,
                'total_time' => $totalTime,
                'writes_per_second' => $iterations / $totalTime,
                'success_rate' => ($successCount / $iterations) * 100
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel test di stress: ' . $e->getMessage()
            ], 500);
        }
    }
}
