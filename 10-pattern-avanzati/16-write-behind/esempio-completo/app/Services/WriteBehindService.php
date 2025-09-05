<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessLogBatch;

class WriteBehindService
{
    protected $cachePrefix = 'write_behind';
    protected $cacheTtl = 3600; // 1 ora
    protected $batchSize = 50; // Dimensione del batch
    protected $queueName = 'default';

    /**
     * Scrittura immediata in cache e coda per database
     */
    public function write($key, $data, $table = 'log_entries')
    {
        try {
            // Genera ID temporaneo
            $id = uniqid('wb_', true);
            $data['id'] = $id;
            $data['created_at'] = now();
            $data['updated_at'] = now();
            
            // Scrittura immediata in cache
            $cacheKey = $this->getCacheKey($key, $id);
            Cache::put($cacheKey, $data, $this->cacheTtl);
            
            // Aggiunge alla coda per scrittura asincrona
            Queue::push(new ProcessLogBatch($data), null, $this->queueName);
            
            Log::info("Write-Behind: Dati scritti in cache e coda", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return $id;
            
        } catch (\Exception $e) {
            Log::error("Write-Behind Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aggiornamento immediato in cache e coda per database
     */
    public function update($key, $id, $data, $table = 'log_entries')
    {
        try {
            $cacheKey = $this->getCacheKey($key, $id);
            
            // Aggiorna la cache
            $cached = Cache::get($cacheKey, []);
            $updatedData = array_merge($cached, $data, ['updated_at' => now()]);
            Cache::put($cacheKey, $updatedData, $this->cacheTtl);
            
            // Aggiunge alla coda per aggiornamento asincrono
            Queue::push(new ProcessLogBatch($updatedData, 'update'), null, $this->queueName);
            
            Log::info("Write-Behind: Dati aggiornati in cache e coda", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Write-Behind Update Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminazione immediata da cache e coda per database
     */
    public function delete($key, $id, $table = 'log_entries')
    {
        try {
            $cacheKey = $this->getCacheKey($key, $id);
            
            // Rimuove dalla cache
            Cache::forget($cacheKey);
            
            // Aggiunge alla coda per eliminazione asincrona
            Queue::push(new ProcessLogBatch(['id' => $id], 'delete'), null, $this->queueName);
            
            Log::info("Write-Behind: Dati eliminati da cache e coda", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Write-Behind Delete Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lettura dalla cache con fallback al database
     */
    public function read($key, $id, $table = 'log_entries')
    {
        $cacheKey = $this->getCacheKey($key, $id);
        
        // Prova prima la cache
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            Log::info("Write-Behind: Dati letti dalla cache", [
                'key' => $cacheKey
            ]);
            return $cached;
        }
        
        // Fallback al database
        $data = DB::table($table)->where('id', $id)->first();
        
        if ($data) {
            // Scrive in cache per le prossime letture
            Cache::put($cacheKey, (array) $data, $this->cacheTtl);
            
            Log::info("Write-Behind: Dati letti dal database e scritti in cache", [
                'key' => $cacheKey
            ]);
        }
        
        return $data ? (array) $data : null;
    }

    /**
     * Scrittura in batch per efficienza
     */
    public function writeBatch($key, $dataArray, $table = 'log_entries')
    {
        $results = [];
        
        foreach ($dataArray as $data) {
            try {
                $id = $this->write($key, $data, $table);
                $results[] = ['id' => $id, 'status' => 'success'];
            } catch (\Exception $e) {
                $results[] = ['id' => null, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Test completo del pattern Write-Behind
     */
    public function testWriteBehind()
    {
        $results = [];
        
        try {
            // Test 1: Scrittura singola
            $testData = [
                'level' => 'info',
                'message' => 'Test Write-Behind',
                'context' => ['test' => true],
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent'
            ];
            
            $id = $this->write('log', $testData);
            $results['write'] = $id ? 'success' : 'failed';
            
            // Test 2: Lettura dalla cache
            $cached = $this->read('log', $id);
            $results['cache_read'] = $cached ? 'success' : 'failed';
            
            // Test 3: Aggiornamento
            $updateData = ['message' => 'Updated Test Write-Behind'];
            $updated = $this->update('log', $id, $updateData);
            $results['update'] = $updated ? 'success' : 'failed';
            
            // Test 4: Test di performance
            $start = microtime(true);
            $batchData = [];
            for ($i = 0; $i < 100; $i++) {
                $batchData[] = [
                    'level' => 'info',
                    'message' => "Performance test {$i}",
                    'context' => ['iteration' => $i],
                    'user_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Agent'
                ];
            }
            $batchResults = $this->writeBatch('log', $batchData);
            $results['performance'] = microtime(true) - $start;
            $results['batch_success'] = count(array_filter($batchResults, fn($r) => $r['status'] === 'success'));
            
            // Test 5: Eliminazione
            $deleted = $this->delete('log', $id);
            $results['delete'] = $deleted ? 'success' : 'failed';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Genera la chiave di cache
     */
    private function getCacheKey($key, $id)
    {
        return "{$this->cachePrefix}:{$key}:{$id}";
    }

    /**
     * Ottiene statistiche del pattern
     */
    public function getStats()
    {
        return [
            'cache_prefix' => $this->cachePrefix,
            'cache_ttl' => $this->cacheTtl,
            'batch_size' => $this->batchSize,
            'queue_name' => $this->queueName,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Pulisce la cache per una chiave specifica
     */
    public function clearCache($key, $id = null)
    {
        if ($id) {
            $cacheKey = $this->getCacheKey($key, $id);
            Cache::forget($cacheKey);
        } else {
            // Implementazione semplificata - in produzione usare pattern matching
            Cache::forget($key);
        }
        
        Log::info("Write-Behind: Cache pulita", [
            'key' => $key,
            'id' => $id
        ]);
    }

    /**
     * Ottiene informazioni sulla coda
     */
    public function getQueueInfo()
    {
        try {
            // Implementazione semplificata - in produzione usare Redis o database
            return [
                'queue_name' => $this->queueName,
                'status' => 'active',
                'timestamp' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'queue_name' => $this->queueName,
                'status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }
}
