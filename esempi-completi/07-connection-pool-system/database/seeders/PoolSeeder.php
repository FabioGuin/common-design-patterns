<?php

namespace Database\Seeders;

use App\Services\ConnectionPool;
use App\Services\ResourcePool;
use App\Services\PoolManager;
use App\Models\FileConnection;
use App\Models\CacheConnection;
use Illuminate\Database\Seeder;

class PoolSeeder extends Seeder
{
    public function run(): void
    {
        $poolManager = new PoolManager();
        
        // Crea pool di connessioni database
        $poolManager->addPool('database', new ConnectionPool('mysql', 10));
        
        // Crea pool di connessioni file
        $poolManager->addPool('files', new ResourcePool(FileConnection::class, 5, ['/tmp/test.txt', 'w']));
        
        // Crea pool di connessioni cache
        $poolManager->addPool('cache', new ResourcePool(CacheConnection::class, 8, ['default']));
        
        // Test dei pool
        $this->testPools($poolManager);
    }

    private function testPools(PoolManager $poolManager): void
    {
        // Test database pool
        try {
            $connection = $poolManager->acquire('database', 'seeder_test');
            $result = $connection->query('SELECT 1 as test');
            $poolManager->release('database', $connection);
            
            $this->command->info('Database pool test: SUCCESS');
        } catch (\Exception $e) {
            $this->command->error('Database pool test: FAILED - ' . $e->getMessage());
        }
        
        // Test file pool
        try {
            $file = $poolManager->acquire('files', 'seeder_test');
            $file->write('Test data from seeder');
            $poolManager->release('files', $file);
            
            $this->command->info('File pool test: SUCCESS');
        } catch (\Exception $e) {
            $this->command->error('File pool test: FAILED - ' . $e->getMessage());
        }
        
        // Test cache pool
        try {
            $cache = $poolManager->acquire('cache', 'seeder_test');
            $cache->set('test_key', 'test_value', 60);
            $value = $cache->get('test_key');
            $poolManager->release('cache', $cache);
            
            $this->command->info('Cache pool test: SUCCESS');
        } catch (\Exception $e) {
            $this->command->error('Cache pool test: FAILED - ' . $e->getMessage());
        }
        
        // Mostra statistiche
        $stats = $poolManager->getAllStats();
        $this->command->info('Pool Statistics:');
        foreach ($stats as $poolName => $poolStats) {
            $this->command->line("  {$poolName}: {$poolStats['in_use']}/{$poolStats['total']} in use");
        }
    }
}
