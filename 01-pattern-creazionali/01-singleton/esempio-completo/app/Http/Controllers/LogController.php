<?php

namespace App\Http\Controllers;

use App\Services\Logger\LoggerService;
use App\Services\Logger\LogLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller per la gestione dei logs via API
 * Dimostra l'utilizzo del LoggerService singleton
 */
class LogController extends Controller
{
    private LoggerService $logger;

    public function __construct()
    {
        // Ottiene l'istanza singleton del logger
        $this->logger = LoggerService::getInstance();
    }

    /**
     * Lista tutti i logs
     */
    public function index(): JsonResponse
    {
        $logs = $this->logger->getLogs();
        
        return response()->json([
            'success' => true,
            'data' => array_map(fn($log) => $log->toArray(), $logs),
            'total' => count($logs)
        ]);
    }

    /**
     * Lista i logs per livello specifico
     */
    public function getByLevel(string $level): JsonResponse
    {
        try {
            $logLevel = LogLevel::from($level);
            $logs = $this->logger->getLogsByLevel($logLevel);
            
            return response()->json([
                'success' => true,
                'data' => array_map(fn($log) => $log->toArray(), $logs),
                'level' => $level,
                'total' => count($logs)
            ]);
        } catch (\ValueError $e) {
            return response()->json([
                'success' => false,
                'error' => "Invalid log level: {$level}",
                'valid_levels' => LogLevel::getAllLevels()
            ], 400);
        }
    }

    /**
     * Crea un nuovo log
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'level' => 'required|string|in:' . implode(',', LogLevel::getAllLevels()),
            'message' => 'required|string|max:1000',
            'context' => 'array'
        ]);

        try {
            $level = LogLevel::from($request->level);
            $this->logger->log($level, $request->message, $request->context ?? []);
            
            return response()->json([
                'success' => true,
                'message' => 'Log created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche dei logs
     */
    public function stats(): JsonResponse
    {
        $stats = $this->logger->getStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Cancella tutti i logs
     */
    public function destroy(): JsonResponse
    {
        $this->logger->clearLogs();
        
        return response()->json([
            'success' => true,
            'message' => 'All logs cleared successfully'
        ]);
    }

    /**
     * Test del singleton pattern
     */
    public function testSingleton(): JsonResponse
    {
        // Crea due istanze per verificare che siano la stessa
        $logger1 = LoggerService::getInstance();
        $logger2 = LoggerService::getInstance();
        
        // Aggiunge un log di test
        $logger1->info('Singleton test log', ['test' => true]);
        
        // Verifica che entrambe le istanze abbiano lo stesso log
        $logs1 = $logger1->getLogs();
        $logs2 = $logger2->getLogs();
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_same_instance' => $logger1 === $logger2,
                'logs_count_1' => count($logs1),
                'logs_count_2' => count($logs2),
                'logs_are_same' => $logs1 === $logs2,
                'last_log' => end($logs1)?->toArray()
            ]
        ]);
    }

    /**
     * Test di tutti i livelli di log
     */
    public function testAllLevels(): JsonResponse
    {
        $this->logger->debug('Debug message', ['level' => 'debug']);
        $this->logger->info('Info message', ['level' => 'info']);
        $this->logger->warning('Warning message', ['level' => 'warning']);
        $this->logger->error('Error message', ['level' => 'error']);
        $this->logger->critical('Critical message', ['level' => 'critical']);
        
        return response()->json([
            'success' => true,
            'message' => 'All log levels tested successfully',
            'stats' => $this->logger->getStats()
        ]);
    }
}
