<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer'
    ];

    /**
     * Override del metodo save per implementare Write-Through Pattern
     */
    public function save(array $options = [])
    {
        // Salva nel database
        $result = parent::save($options);
        
        if ($result) {
            // Scrittura simultanea nella cache (Write-Through)
            $this->writeToCache();
        }
        
        return $result;
    }

    /**
     * Override del metodo update per implementare Write-Through Pattern
     */
    public function update(array $attributes = [], array $options = [])
    {
        $result = parent::update($attributes, $options);
        
        if ($result) {
            // Scrittura simultanea nella cache (Write-Through)
            $this->writeToCache();
        }
        
        return $result;
    }

    /**
     * Override del metodo delete per implementare Write-Through Pattern
     */
    public function delete()
    {
        $result = parent::delete();
        
        if ($result) {
            // Rimuove dalla cache quando eliminato
            $this->removeFromCache();
        }
        
        return $result;
    }

    /**
     * Scrittura simultanea in cache e database
     */
    public function writeToCache()
    {
        try {
            // Scrittura nella cache
            Cache::put($this->getCacheKey(), $this->toArray(), 3600); // 1 ora
            
            // Log per debugging
            \Log::info("Write-Through: Product {$this->id} scritto in cache");
            
        } catch (\Exception $e) {
            // Se la cache fallisce, invalida per mantenere coerenza
            \Log::error("Write-Through Cache Error: " . $e->getMessage());
            $this->removeFromCache();
        }
    }

    /**
     * Rimuove dalla cache
     */
    public function removeFromCache()
    {
        Cache::forget($this->getCacheKey());
        \Log::info("Write-Through: Product {$this->id} rimosso dalla cache");
    }

    /**
     * Lettura dalla cache con fallback al database
     */
    public static function findWithCache($id)
    {
        $cacheKey = "product:{$id}";
        
        // Prova prima la cache
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            \Log::info("Write-Through: Product {$id} letto dalla cache");
            return new static($cached);
        }
        
        // Fallback al database
        $product = static::find($id);
        
        if ($product) {
            // Scrive in cache per le prossime letture
            $product->writeToCache();
        }
        
        return $product;
    }

    /**
     * Genera la chiave di cache
     */
    private function getCacheKey()
    {
        return "product:{$this->id}";
    }

    /**
     * Test del pattern Write-Through
     */
    public static function testWriteThrough()
    {
        $results = [];
        
        // Test 1: Creazione con Write-Through
        $product = new static([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10
        ]);
        
        $product->save();
        $results['creation'] = $product->id;
        
        // Test 2: Lettura dalla cache
        $cached = static::findWithCache($product->id);
        $results['cache_read'] = $cached ? 'success' : 'failed';
        
        // Test 3: Aggiornamento con Write-Through
        $product->update(['price' => 149.99]);
        $results['update'] = 'success';
        
        // Test 4: Verifica coerenza
        $updated = static::findWithCache($product->id);
        $results['consistency'] = $updated->price == 149.99 ? 'consistent' : 'inconsistent';
        
        // Test 5: Eliminazione con Write-Through
        $product->delete();
        $results['deletion'] = 'success';
        
        // Test 6: Verifica rimozione dalla cache
        $deleted = static::findWithCache($product->id);
        $results['cache_removal'] = $deleted ? 'failed' : 'success';
        
        return $results;
    }
}
