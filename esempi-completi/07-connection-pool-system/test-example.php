<?php

require_once 'vendor/autoload.php';

use App\Services\PoolManager;
use App\Services\DatabaseService;

// Simula l'ambiente Laravel per il test
if (!function_exists('config')) {
    function config($key, $default = null) {
        // Configurazione di test
        $config = [
            'database.connections.mysql' => [
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'test_db',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
            ]
        ];
        
        return $config[$key] ?? $default;
    }
}

if (!function_exists('now')) {
    function now() {
        return new class {
            public function toISOString() {
                return date('c');
            }
        };
    }
}

if (!function_exists('Log')) {
    class Log {
        public static function info($message) {
            echo "[INFO] " . $message . "\n";
        }
        
        public static function warning($message) {
            echo "[WARNING] " . $message . "\n";
        }
        
        public static function error($message) {
            echo "[ERROR] " . $message . "\n";
        }
    }
}

echo "=== Connection Pool System Test ===\n\n";

try {
    // 1. Crea il pool manager
    echo "1. Creazione Pool Manager...\n";
    $poolManager = PoolManager::getInstance();
    
    // 2. Crea un pool di test
    echo "2. Creazione pool di test...\n";
    $pool = $poolManager->createPool('test-pool', 'mysql', 3);
    echo "   Pool creato con successo!\n";
    
    // 3. Test di acquisizione e rilascio
    echo "3. Test acquisizione connessioni...\n";
    $connection1 = $pool->acquire();
    echo "   Connessione 1 acquisita\n";
    
    $connection2 = $pool->acquire();
    echo "   Connessione 2 acquisita\n";
    
    $stats = $pool->getStats();
    echo "   Statistiche: {$stats['in_use']} in uso, {$stats['available']} disponibili\n";
    
    // 4. Rilascia le connessioni
    echo "4. Rilascio connessioni...\n";
    $pool->release($connection1);
    $pool->release($connection2);
    
    $stats = $pool->getStats();
    echo "   Statistiche: {$stats['in_use']} in uso, {$stats['available']} disponibili\n";
    
    // 5. Test del DatabaseService
    echo "5. Test DatabaseService...\n";
    $service = new DatabaseService('test-pool');
    
    // Simula alcune operazioni
    echo "   DatabaseService creato con successo\n";
    
    $serviceStats = $service->getPoolStats();
    echo "   Statistiche pool dal service: {$serviceStats['max_size']} max size\n";
    
    // 6. Test di performance
    echo "6. Test di performance...\n";
    $iterations = 10;
    $startTime = microtime(true);
    
    for ($i = 0; $i < $iterations; $i++) {
        $connection = $pool->acquire();
        // Simula una query
        usleep(1000); // 1ms
        $pool->release($connection);
    }
    
    $endTime = microtime(true);
    $duration = $endTime - $startTime;
    
    echo "   {$iterations} operazioni completate in " . round($duration, 4) . " secondi\n";
    echo "   " . round($iterations / $duration, 2) . " operazioni al secondo\n";
    
    // 7. Test stato di salute
    echo "7. Test stato di salute...\n";
    $health = $pool->getHealthStatus();
    echo "   Stato: {$health['status']}\n";
    echo "   Utilizzo: {$health['stats']['utilization_percentage']}%\n";
    
    // 8. Test statistiche globali
    echo "8. Test statistiche globali...\n";
    $globalStats = $poolManager->getGlobalStats();
    echo "   Pool totali: {$globalStats['total_pools']}\n";
    echo "   Connessioni totali: {$globalStats['total_in_use']} in uso, {$globalStats['total_available']} disponibili\n";
    
    // 9. Test reset
    echo "9. Test reset pool...\n";
    $pool->reset();
    $stats = $pool->getStats();
    echo "   Dopo reset: {$stats['in_use']} in uso, {$stats['available']} disponibili\n";
    
    echo "\n=== Test completato con successo! ===\n";
    
} catch (Exception $e) {
    echo "\n=== ERRORE ===\n";
    echo "Errore: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}