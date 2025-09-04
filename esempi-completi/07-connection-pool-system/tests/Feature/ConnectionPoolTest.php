<?php

use App\Services\ConnectionPool;
use App\Services\PoolManager;
use App\Services\DatabaseService;
use PDO;
use PDOException;

beforeEach(function () {
    // Pulisci tutti i pool prima di ogni test
    $poolManager = PoolManager::getInstance();
    $pools = $poolManager->getAllPools();
    foreach ($pools as $name => $pool) {
        $poolManager->removePool($name);
    }
});

describe('ConnectionPool', function () {
    
    test('può creare un pool di connessioni', function () {
    $pool = new ConnectionPool('mysql', 5);
    
        expect($pool)->toBeInstanceOf(ConnectionPool::class);
        expect($pool->getStats())->toHaveKey('max_size');
        expect($pool->getStats()['max_size'])->toBe(5);
    });
    
    test('può acquisire e rilasciare connessioni', function () {
    $pool = new ConnectionPool('mysql', 3);
    
    // Acquisisci una connessione
        $connection = $pool->acquire();
        expect($connection)->toBeInstanceOf(PDO::class);
    
    $stats = $pool->getStats();
    expect($stats['in_use'])->toBe(1);
    expect($stats['available'])->toBe(0);
    
    // Rilascia la connessione
    $pool->release($connection);
    
    $stats = $pool->getStats();
    expect($stats['in_use'])->toBe(0);
    expect($stats['available'])->toBe(1);
});

    test('può gestire multiple connessioni', function () {
        $pool = new ConnectionPool('mysql', 3);
        
        $connections = [];
        
        // Acquisisci 3 connessioni
        for ($i = 0; $i < 3; $i++) {
            $connections[] = $pool->acquire();
        }
    
    $stats = $pool->getStats();
        expect($stats['in_use'])->toBe(3);
        expect($stats['available'])->toBe(0);
        
        // Rilascia tutte le connessioni
        foreach ($connections as $connection) {
            $pool->release($connection);
        }
        
        $stats = $pool->getStats();
        expect($stats['in_use'])->toBe(0);
        expect($stats['available'])->toBe(3);
    });
    
    test('lancia eccezione quando il pool è esaurito', function () {
        $pool = new ConnectionPool('mysql', 2);
        
        // Acquisisci 2 connessioni (limite massimo)
        $connection1 = $pool->acquire();
        $connection2 = $pool->acquire();
        
        // Tentativo di acquisire una terza connessione dovrebbe fallire
        expect(fn() => $pool->acquire())->toThrow(Exception::class, 'Connection pool esaurito');
    });
    
    test('può eseguire query con le connessioni', function () {
        $pool = new ConnectionPool('mysql', 2);
        
        $connection = $pool->acquire();
        
        try {
            $stmt = $connection->prepare('SELECT 1 as test_value');
            $stmt->execute();
            $result = $stmt->fetch();
            
            expect($result['test_value'])->toBe(1);
        } finally {
            $pool->release($connection);
        }
    });
    
    test('può resettare il pool', function () {
        $pool = new ConnectionPool('mysql', 3);
        
        // Acquisisci alcune connessioni
        $connection1 = $pool->acquire();
        $connection2 = $pool->acquire();
        
        $stats = $pool->getStats();
        expect($stats['in_use'])->toBe(2);
        
        // Reset del pool
        $pool->reset();
        
        $stats = $pool->getStats();
        expect($stats['in_use'])->toBe(0);
        expect($stats['available'])->toBe(0);
    });
    
    test('fornisce statistiche corrette', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    $stats = $pool->getStats();
        
        expect($stats)->toHaveKeys([
            'available',
            'in_use',
            'total',
            'max_size',
            'connection_name',
            'utilization_percentage'
        ]);
    
    expect($stats['max_size'])->toBe(5);
        expect($stats['connection_name'])->toBe('mysql');
        expect($stats['utilization_percentage'])->toBe(0.0);
    });
    
    test('fornisce stato di salute corretto', function () {
        $pool = new ConnectionPool('mysql', 5);
        
        $health = $pool->getHealthStatus();
        
        expect($health)->toHaveKeys([
            'status',
            'stats',
            'timestamp'
        ]);
        
        expect($health['status'])->toBe('healthy');
    });
    
});

describe('PoolManager', function () {
    
    test('può creare e gestire pool multipli', function () {
        $poolManager = PoolManager::getInstance();
        
        // Crea due pool
        $pool1 = $poolManager->createPool('pool1', 'mysql', 3);
        $pool2 = $poolManager->createPool('pool2', 'mysql', 5);
        
        expect($pool1)->toBeInstanceOf(ConnectionPool::class);
        expect($pool2)->toBeInstanceOf(ConnectionPool::class);
        
        // Verifica che i pool siano diversi
        expect($pool1)->not->toBe($pool2);
        
        // Verifica le statistiche
        $stats = $poolManager->getAllStats();
        expect($stats)->toHaveKeys(['pool1', 'pool2']);
        expect($stats['pool1']['max_size'])->toBe(3);
        expect($stats['pool2']['max_size'])->toBe(5);
    });
    
    test('può recuperare un pool esistente', function () {
        $poolManager = PoolManager::getInstance();
        
        $poolManager->createPool('test-pool', 'mysql', 3);
        $pool = $poolManager->getPool('test-pool');
        
        expect($pool)->toBeInstanceOf(ConnectionPool::class);
        expect($pool->getStats()['max_size'])->toBe(3);
    });
    
    test('lancia eccezione per pool inesistente', function () {
        $poolManager = PoolManager::getInstance();
        
        expect(fn() => $poolManager->getPool('pool-inesistente'))
            ->toThrow(Exception::class, 'Pool \'pool-inesistente\' non trovato');
    });
    
    test('può rimuovere un pool', function () {
        $poolManager = PoolManager::getInstance();
        
        $poolManager->createPool('temp-pool', 'mysql', 3);
        expect($poolManager->getAllPools())->toHaveKey('temp-pool');
        
        $poolManager->removePool('temp-pool');
        expect($poolManager->getAllPools())->not->toHaveKey('temp-pool');
    });
    
    test('può resettare tutti i pool', function () {
        $poolManager = PoolManager::getInstance();
        
        $pool1 = $poolManager->createPool('pool1', 'mysql', 3);
        $pool2 = $poolManager->createPool('pool2', 'mysql', 5);
        
        // Acquisisci connessioni
        $pool1->acquire();
        $pool2->acquire();
        
        // Reset tutti i pool
        $poolManager->resetAllPools();
        
        $stats = $poolManager->getAllStats();
        expect($stats['pool1']['in_use'])->toBe(0);
        expect($stats['pool2']['in_use'])->toBe(0);
    });
    
    test('fornisce statistiche globali', function () {
        $poolManager = PoolManager::getInstance();
        
        $poolManager->createPool('pool1', 'mysql', 3);
        $poolManager->createPool('pool2', 'mysql', 5);
        
        $globalStats = $poolManager->getGlobalStats();
        
        expect($globalStats)->toHaveKeys([
            'total_pools',
            'total_available',
            'total_in_use',
            'total_max_size',
            'global_utilization_percentage',
            'timestamp'
        ]);
        
        expect($globalStats['total_pools'])->toBe(2);
        expect($globalStats['total_max_size'])->toBe(8);
    });
    
});

describe('DatabaseService', function () {
    
    beforeEach(function () {
        // Crea un pool per i test
        $poolManager = PoolManager::getInstance();
        $poolManager->createPool('test-pool', 'mysql', 3);
    });
    
    test('può essere istanziato con un pool', function () {
        $service = new DatabaseService('test-pool');
        
        expect($service)->toBeInstanceOf(DatabaseService::class);
    });
    
    test('può eseguire query di base', function () {
        $service = new DatabaseService('test-pool');
        
        // Test di una query semplice
        $result = $service->getUsersByRole('user', 1);
        
        expect($result)->toBeArray();
    });
    
    test('può processare utenti in batch', function () {
        $service = new DatabaseService('test-pool');
        
        $result = $service->processUsers([1, 2, 3]);
        
        expect($result)->toHaveKeys([
            'users',
            'errors',
            'total_processed',
            'successful',
            'failed'
        ]);
        
        expect($result['total_processed'])->toBe(3);
    });
    
    test('può eseguire query batch', function () {
        $service = new DatabaseService('test-pool');
        
        $queries = [
            ['sql' => 'SELECT 1 as test1', 'params' => []],
            ['sql' => 'SELECT 2 as test2', 'params' => []],
        ];
        
        $result = $service->executeBatchQueries($queries);
        
        expect($result)->toHaveKeys([
            'results',
            'errors',
            'total_queries',
            'successful',
            'failed'
        ]);
        
        expect($result['total_queries'])->toBe(2);
    });
    
    test('fornisce statistiche del pool', function () {
        $service = new DatabaseService('test-pool');
        
        $stats = $service->getPoolStats();
        
        expect($stats)->toHaveKey('max_size');
        expect($stats['max_size'])->toBe(3);
    });
    
    test('fornisce stato di salute del pool', function () {
        $service = new DatabaseService('test-pool');
        
        $health = $service->getPoolHealth();
        
        expect($health)->toHaveKeys([
            'status',
            'stats',
            'timestamp'
        ]);
    });
    
});

describe('Performance Tests', function () {
    
    test('pool è più efficiente di creare connessioni ogni volta', function () {
        $pool = new ConnectionPool('mysql', 5);
        
        $iterations = 50;
        
        // Test con pool
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $connection = $pool->acquire();
            $stmt = $connection->prepare('SELECT ? as iteration');
            $stmt->execute([$i]);
            $result = $stmt->fetch();
            $pool->release($connection);
        }
        $poolTime = microtime(true) - $startTime;
        
        // Test senza pool (simulato)
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            // Simula il costo di creazione connessione
            usleep(1000); // 1ms
            $stmt = $pool->acquire()->prepare('SELECT ? as iteration');
            $stmt->execute([$i]);
            $result = $stmt->fetch();
            $pool->release($pool->acquire());
        }
        $noPoolTime = microtime(true) - $startTime;
        
        // Il pool dovrebbe essere più veloce
        expect($poolTime)->toBeLessThan($noPoolTime);
    });
    
});