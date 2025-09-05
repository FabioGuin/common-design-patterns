<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WriteThroughService
{
    protected $cachePrefix = 'write_through';
    protected $cacheTtl = 3600; // 1 ora

    /**
     * Scrittura simultanea in cache e database
     */
    public function write($key, $data, $table = 'products')
    {
        try {
            DB::beginTransaction();
            
            // Scrittura nel database
            $id = DB::table($table)->insertGetId($data);
            
            // Scrittura simultanea nella cache
            $cacheKey = $this->getCacheKey($key, $id);
            Cache::put($cacheKey, array_merge($data, ['id' => $id]), $this->cacheTtl);
            
            DB::commit();
            
            Log::info("Write-Through: Dati scritti in cache e database", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return $id;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Invalida la cache se il database fallisce
            $this->invalidateCache($key);
            
            Log::error("Write-Through Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aggiornamento simultaneo in cache e database
     */
    public function update($key, $id, $data, $table = 'products')
    {
        try {
            DB::beginTransaction();
            
            // Aggiornamento nel database
            $updated = DB::table($table)
                ->where('id', $id)
                ->update($data);
            
            if ($updated) {
                // Aggiornamento simultaneo nella cache
                $cacheKey = $this->getCacheKey($key, $id);
                $cached = Cache::get($cacheKey, []);
                $updatedData = array_merge($cached, $data);
                Cache::put($cacheKey, $updatedData, $this->cacheTtl);
            }
            
            DB::commit();
            
            Log::info("Write-Through: Dati aggiornati in cache e database", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return $updated > 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Invalida la cache se il database fallisce
            $this->invalidateCache($key, $id);
            
            Log::error("Write-Through Update Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminazione simultanea da cache e database
     */
    public function delete($key, $id, $table = 'products')
    {
        try {
            DB::beginTransaction();
            
            // Eliminazione dal database
            $deleted = DB::table($table)
                ->where('id', $id)
                ->delete();
            
            if ($deleted) {
                // Eliminazione simultanea dalla cache
                $cacheKey = $this->getCacheKey($key, $id);
                Cache::forget($cacheKey);
            }
            
            DB::commit();
            
            Log::info("Write-Through: Dati eliminati da cache e database", [
                'key' => $cacheKey,
                'id' => $id
            ]);
            
            return $deleted > 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Write-Through Delete Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lettura dalla cache con fallback al database
     */
    public function read($key, $id, $table = 'products')
    {
        $cacheKey = $this->getCacheKey($key, $id);
        
        // Prova prima la cache
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            Log::info("Write-Through: Dati letti dalla cache", [
                'key' => $cacheKey
            ]);
            return $cached;
        }
        
        // Fallback al database
        $data = DB::table($table)->where('id', $id)->first();
        
        if ($data) {
            // Scrive in cache per le prossime letture
            Cache::put($cacheKey, (array) $data, $this->cacheTtl);
            
            Log::info("Write-Through: Dati letti dal database e scritti in cache", [
                'key' => $cacheKey
            ]);
        }
        
        return $data ? (array) $data : null;
    }

    /**
     * Test completo del pattern Write-Through
     */
    public function testWriteThrough()
    {
        $results = [];
        
        try {
            // Test 1: Scrittura
            $testData = [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 99.99,
                'stock' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            $id = $this->write('product', $testData);
            $results['write'] = $id ? 'success' : 'failed';
            
            // Test 2: Lettura dalla cache
            $cached = $this->read('product', $id);
            $results['cache_read'] = $cached ? 'success' : 'failed';
            
            // Test 3: Aggiornamento
            $updateData = ['price' => 149.99, 'updated_at' => now()];
            $updated = $this->update('product', $id, $updateData);
            $results['update'] = $updated ? 'success' : 'failed';
            
            // Test 4: Verifica coerenza
            $final = $this->read('product', $id);
            $results['consistency'] = $final && $final['price'] == 149.99 ? 'consistent' : 'inconsistent';
            
            // Test 5: Eliminazione
            $deleted = $this->delete('product', $id);
            $results['delete'] = $deleted ? 'success' : 'failed';
            
            // Test 6: Verifica rimozione dalla cache
            $removed = $this->read('product', $id);
            $results['cache_removal'] = $removed ? 'failed' : 'success';
            
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
     * Invalida la cache
     */
    private function invalidateCache($key, $id = null)
    {
        if ($id) {
            $cacheKey = $this->getCacheKey($key, $id);
            Cache::forget($cacheKey);
        } else {
            // Invalida tutte le chiavi che iniziano con il pattern
            $pattern = "{$this->cachePrefix}:{$key}:*";
            // Nota: Redis supporta pattern matching, implementazione semplificata
            Cache::forget($key);
        }
        
        Log::info("Write-Through: Cache invalidata", [
            'key' => $key,
            'id' => $id
        ]);
    }

    /**
     * Ottiene statistiche del pattern
     */
    public function getStats()
    {
        return [
            'cache_prefix' => $this->cachePrefix,
            'cache_ttl' => $this->cacheTtl,
            'timestamp' => now()->toISOString()
        ];
    }
}
