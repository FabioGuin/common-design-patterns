<?php

use App\Services\ConnectionPool;
use App\Services\ResourcePool;
use App\Services\PoolManager;
use App\Models\DatabaseConnection;
use App\Models\FileConnection;
use App\Models\CacheConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('può creare e gestire un pool di connessioni database', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    expect($pool->getMaxSize())->toBe(5);
    expect($pool->getConnectionName())->toBe('mysql');
    
    $stats = $pool->getStats();
    expect($stats['max_size'])->toBe(5);
    expect($stats['available'])->toBe(0);
    expect($stats['in_use'])->toBe(0);
    expect($stats['total'])->toBe(0);
});

test('può acquisire e rilasciare connessioni dal pool', function () {
    $pool = new ConnectionPool('mysql', 3);
    
    // Acquisisci una connessione
    $connection = $pool->acquire('test_user');
    
    expect($connection)->toBeInstanceOf(DatabaseConnection::class);
    expect($connection->isInUse())->toBeTrue();
    expect($connection->getAcquiredBy())->toBe('test_user');
    
    $stats = $pool->getStats();
    expect($stats['in_use'])->toBe(1);
    expect($stats['available'])->toBe(0);
    
    // Rilascia la connessione
    $pool->release($connection);
    
    expect($connection->isInUse())->toBeFalse();
    
    $stats = $pool->getStats();
    expect($stats['in_use'])->toBe(0);
    expect($stats['available'])->toBe(1);
});

test('può gestire il limite massimo del pool', function () {
    $pool = new ConnectionPool('mysql', 2);
    
    // Acquisisci tutte le connessioni disponibili
    $connection1 = $pool->acquire('user1');
    $connection2 = $pool->acquire('user2');
    
    $stats = $pool->getStats();
    expect($stats['in_use'])->toBe(2);
    expect($stats['total'])->toBe(2);
    
    // Tentativo di acquisire una terza connessione dovrebbe fallire
    expect(function () use ($pool) {
        $pool->acquire('user3');
    })->toThrow(Exception::class, 'Connection pool esaurito');
});

test('può gestire un pool di risorse generiche', function () {
    $pool = new ResourcePool(FileConnection::class, 3, ['/tmp/test.txt', 'w']);
    
    expect($pool->getMaxSize())->toBe(3);
    expect($pool->getResourceClass())->toBe(FileConnection::class);
    
    $stats = $pool->getStats();
    expect($stats['max_size'])->toBe(3);
    expect($stats['resource_class'])->toBe(FileConnection::class);
});

test('può gestire pool multipli con PoolManager', function () {
    $poolManager = new PoolManager();
    
    // Aggiungi pool
    $poolManager->addPool('database', new ConnectionPool('mysql', 5));
    $poolManager->addPool('files', new ResourcePool(FileConnection::class, 3, ['/tmp/test.txt', 'w']));
    
    expect($poolManager->getTotalPools())->toBe(2);
    expect($poolManager->hasPool('database'))->toBeTrue();
    expect($poolManager->hasPool('files'))->toBeTrue();
    expect($poolManager->hasPool('nonexistent'))->toBeFalse();
});

test('può acquisire risorse da pool multipli', function () {
    $poolManager = new PoolManager();
    $poolManager->addPool('database', new ConnectionPool('mysql', 5));
    $poolManager->addPool('files', new ResourcePool(FileConnection::class, 3, ['/tmp/test.txt', 'w']));
    
    // Acquisisci da pool diversi
    $connection = $poolManager->acquire('database', 'test_user');
    $file = $poolManager->acquire('files', 'test_user');
    
    expect($connection)->toBeInstanceOf(DatabaseConnection::class);
    expect($file)->toBeInstanceOf(FileConnection::class);
    
    // Rilascia le risorse
    $poolManager->release('database', $connection);
    $poolManager->release('files', $file);
    
    $stats = $poolManager->getAllStats();
    expect($stats['database']['in_use'])->toBe(0);
    expect($stats['files']['in_use'])->toBe(0);
});

test('può ottenere statistiche dettagliate', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    // Acquisisci e rilascia alcune connessioni
    $connection1 = $pool->acquire('user1');
    $connection2 = $pool->acquire('user2');
    $pool->release($connection1);
    
    $stats = $pool->getStats();
    
    expect($stats['max_size'])->toBe(5);
    expect($stats['available'])->toBe(1);
    expect($stats['in_use'])->toBe(1);
    expect($stats['total'])->toBe(2);
    expect($stats['created'])->toBe(2);
    expect($stats['acquired'])->toBe(2);
    expect($stats['released'])->toBe(1);
    expect($stats['utilization'])->toBe(50.0); // 1 su 2 in uso
});

test('può eseguire health check', function () {
    $pool = new ConnectionPool('mysql', 3);
    
    // Acquisisci alcune connessioni
    $connection1 = $pool->acquire('user1');
    $connection2 = $pool->acquire('user2');
    
    $health = $pool->healthCheck();
    
    expect($health['total_connections'])->toBe(2);
    expect($health['healthy'])->toBeGreaterThanOrEqual(0);
    expect($health['unhealthy'])->toBeGreaterThanOrEqual(0);
    expect($health['health_percentage'])->toBeGreaterThanOrEqual(0);
    expect($health['status'])->toBeIn(['healthy', 'degraded', 'unhealthy']);
    
    // Rilascia le connessioni
    $pool->release($connection1);
    $pool->release($connection2);
});

test('può eseguire cleanup del pool', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    // Acquisisci e rilascia alcune connessioni
    $connection1 = $pool->acquire('user1');
    $connection2 = $pool->acquire('user2');
    $pool->release($connection1);
    $pool->release($connection2);
    
    $statsBefore = $pool->getStats();
    expect($statsBefore['available'])->toBe(2);
    
    // Esegui cleanup
    $removed = $pool->cleanup();
    
    $statsAfter = $pool->getStats();
    expect($removed)->toBeGreaterThanOrEqual(0);
});

test('può eseguire reset del pool', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    // Acquisisci e rilascia alcune connessioni
    $connection1 = $pool->acquire('user1');
    $connection2 = $pool->acquire('user2');
    $pool->release($connection1);
    $pool->release($connection2);
    
    // Esegui reset
    $pool->reset();
    
    $stats = $pool->getStats();
    expect($stats['available'])->toBe(0);
    expect($stats['in_use'])->toBe(0);
    expect($stats['total'])->toBe(0);
});

test('può gestire errori durante l\'acquisizione', function () {
    $pool = new ConnectionPool('mysql', 1);
    
    // Acquisisci l'unica connessione disponibile
    $connection = $pool->acquire('user1');
    
    // Tentativo di acquisire una seconda connessione dovrebbe fallire
    expect(function () use ($pool) {
        $pool->acquire('user2');
    })->toThrow(Exception::class, 'Connection pool esaurito');
    
    $stats = $pool->getStats();
    expect($stats['failed'])->toBe(1);
});

test('può tracciare la durata di utilizzo delle risorse', function () {
    $pool = new ConnectionPool('mysql', 5);
    
    $connection = $pool->acquire('test_user');
    
    // Simula utilizzo
    sleep(1);
    
    $duration = $connection->getUsageDuration();
    expect($duration)->toBeGreaterThan(0);
    
    $pool->release($connection);
    
    expect($connection->getUsageDuration())->toBeNull();
});

test('può gestire pool con configurazioni diverse', function () {
    $poolManager = new PoolManager();
    
    // Pool con dimensioni diverse
    $poolManager->addPool('small', new ConnectionPool('mysql', 2));
    $poolManager->addPool('large', new ConnectionPool('mysql', 10));
    
    $stats = $poolManager->getAllStats();
    
    expect($stats['small']['max_size'])->toBe(2);
    expect($stats['large']['max_size'])->toBe(10);
    
    expect($poolManager->getTotalResources())->toBe(0);
    expect($poolManager->getTotalInUse())->toBe(0);
    expect($poolManager->getTotalAvailable())->toBe(0);
});
