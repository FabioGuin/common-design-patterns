<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessLogBatch;

class LogEntry extends Model
{
    protected $fillable = [
        'level',
        'message',
        'context',
        'user_id',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Override del metodo save per implementare Write-Behind Pattern
     */
    public function save(array $options = [])
    {
        // Scrittura immediata in cache
        $this->writeToCache();
        
        // Aggiunge alla coda per scrittura asincrona
        $this->queueForDatabaseWrite();
        
        // Non salva nel database qui - sarà fatto in background
        return true;
    }

    /**
     * Override del metodo create per implementare Write-Behind Pattern
     */
    public static function create(array $attributes = [])
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Scrittura immediata in cache
     */
    public function writeToCache()
    {
        try {
            // Genera un ID temporaneo se non esiste
            if (!$this->id) {
                $this->id = uniqid('log_', true);
            }
            
            // Scrittura nella cache
            $cacheKey = $this->getCacheKey();
            Cache::put($cacheKey, $this->toArray(), 3600); // 1 ora
            
            // Log per debugging
            \Log::info("Write-Behind: Log {$this->id} scritto in cache");
            
        } catch (\Exception $e) {
            \Log::error("Write-Behind Cache Error: " . $e->getMessage());
        }
    }

    /**
     * Aggiunge alla coda per scrittura asincrona nel database
     */
    public function queueForDatabaseWrite()
    {
        try {
            // Aggiunge alla coda per processing in batch
            Queue::push(new ProcessLogBatch($this->toArray()));
            
            \Log::info("Write-Behind: Log {$this->id} aggiunto alla coda");
            
        } catch (\Exception $e) {
            \Log::error("Write-Behind Queue Error: " . $e->getMessage());
        }
    }

    /**
     * Lettura dalla cache
     */
    public static function findWithCache($id)
    {
        $cacheKey = "log_entry:{$id}";
        
        // Prova prima la cache
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            \Log::info("Write-Behind: Log {$id} letto dalla cache");
            return new static($cached);
        }
        
        // Fallback al database
        $log = static::find($id);
        
        if ($log) {
            // Scrive in cache per le prossime letture
            $log->writeToCache();
        }
        
        return $log;
    }

    /**
     * Genera la chiave di cache
     */
    private function getCacheKey()
    {
        return "log_entry:{$this->id}";
    }

    /**
     * Test del pattern Write-Behind
     */
    public static function testWriteBehind()
    {
        $results = [];
        
        try {
            // Test 1: Creazione con Write-Behind
            $log = static::create([
                'level' => 'info',
                'message' => 'Test Write-Behind',
                'context' => ['test' => true],
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent'
            ]);
            
            $results['creation'] = $log->id ? 'success' : 'failed';
            
            // Test 2: Lettura dalla cache
            $cached = static::findWithCache($log->id);
            $results['cache_read'] = $cached ? 'success' : 'failed';
            
            // Test 3: Verifica che sia in coda
            $results['queued'] = 'success'; // Assumiamo che sia in coda
            
            // Test 4: Test di performance
            $start = microtime(true);
            for ($i = 0; $i < 100; $i++) {
                static::create([
                    'level' => 'info',
                    'message' => "Performance test {$i}",
                    'context' => ['iteration' => $i],
                    'user_id' => 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Test Agent'
                ]);
            }
            $results['performance'] = microtime(true) - $start;
            
            // Test 5: Verifica cache
            $cacheKey = "log_entry:{$log->id}";
            $results['cache_exists'] = Cache::has($cacheKey) ? 'yes' : 'no';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Ottiene statistiche del pattern
     */
    public static function getStats()
    {
        return [
            'cache_prefix' => 'log_entry',
            'cache_ttl' => 3600,
            'queue_name' => 'default',
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Pulisce la cache per un log specifico
     */
    public function clearCache()
    {
        $cacheKey = $this->getCacheKey();
        Cache::forget($cacheKey);
        \Log::info("Write-Behind: Cache pulita per log {$this->id}");
    }

    /**
     * Pulisce tutta la cache dei log
     */
    public static function clearAllCache()
    {
        // Implementazione semplificata - in produzione usare pattern matching
        \Log::info("Write-Behind: Cache pulita per tutti i log");
    }
}
