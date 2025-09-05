<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello Product per il Shared Database Anti-pattern
 * 
 * Questo modello dimostra i problemi dell'utilizzo di un database condiviso
 * dove le modifiche al schema impattano altri servizi.
 */
class Product extends Model
{
    protected $connection = 'shared_database';
    protected $table = 'products';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'inventory',
        'sku',
        'weight',
        'dimensions'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'inventory' => 'integer',
        'weight' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relazione con gli item degli ordini
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Verifica se il prodotto è disponibile
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isAvailable()
    {
        return $this->inventory > 0;
    }
    
    /**
     * Verifica se il prodotto ha inventario sufficiente
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function hasSufficientInventory($quantity)
    {
        return $this->inventory >= $quantity;
    }
    
    /**
     * Aggiorna l'inventario del prodotto
     * 
     * Problema: Modifica su tabella condivisa con possibili conflitti
     */
    public function updateInventory($quantityChange)
    {
        $this->inventory += $quantityChange;
        
        if ($this->inventory < 0) {
            throw new \Exception('Insufficient inventory');
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Ottiene le statistiche del prodotto
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public function getStats()
    {
        return [
            'total_orders' => $this->orderItems()->count(),
            'total_quantity_sold' => $this->orderItems()->sum('quantity'),
            'total_revenue' => $this->orderItems()->sum('price'),
            'average_order_quantity' => $this->orderItems()->avg('quantity'),
            'inventory_level' => $this->inventory,
            'is_low_stock' => $this->inventory < 10
        ];
    }
    
    /**
     * Ottiene i prodotti più venduti
     * 
     * Problema: Query complessa su database condiviso
     */
    public static function getTopSelling($limit = 10)
    {
        return static::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Ottiene i prodotti per categoria
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByCategory($category)
    {
        return static::where('category', $category)
            ->where('inventory', '>', 0)
            ->get();
    }
    
    /**
     * Verifica se il prodotto è in stock
     * 
     * Problema: Verifica su tabella condivisa
     */
    public function isInStock()
    {
        return $this->inventory > 0;
    }
    
    /**
     * Ottiene il livello di stock
     * 
     * Problema: Query su tabella condivisa
     */
    public function getStockLevel()
    {
        if ($this->inventory <= 0) {
            return 'out_of_stock';
        } elseif ($this->inventory < 10) {
            return 'low_stock';
        } elseif ($this->inventory < 50) {
            return 'medium_stock';
        } else {
            return 'high_stock';
        }
    }
}
