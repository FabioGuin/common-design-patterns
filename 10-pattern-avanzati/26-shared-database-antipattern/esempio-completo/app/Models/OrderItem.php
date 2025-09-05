<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello OrderItem per il Shared Database Anti-pattern
 * 
 * Questo modello dimostra i problemi dell'utilizzo di un database condiviso
 * dove le modifiche al schema impattano altri servizi.
 */
class OrderItem extends Model
{
    protected $connection = 'shared_database';
    protected $table = 'order_items';
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total'
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relazione con l'ordine
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Relazione con il prodotto
     * 
     * Problema: Relazione diretta con tabella condivisa
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Calcola il totale dell'item
     * 
     * Problema: Calcolo su tabella condivisa
     */
    public function calculateTotal()
    {
        return $this->quantity * $this->price;
    }
    
    /**
     * Verifica se l'item è valido
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function isValid()
    {
        return $this->quantity > 0 && $this->price > 0;
    }
    
    /**
     * Ottiene le statistiche dell'item
     * 
     * Problema: Query complessa su multiple tabelle condivise
     */
    public function getStats()
    {
        return [
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->calculateTotal(),
            'is_valid' => $this->isValid(),
            'product_name' => $this->product ? $this->product->name : 'Unknown',
            'order_id' => $this->order_id
        ];
    }
    
    /**
     * Ottiene gli item per ordine
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByOrder($orderId)
    {
        return static::where('order_id', $orderId)
            ->with('product')
            ->get();
    }
    
    /**
     * Ottiene gli item per prodotto
     * 
     * Problema: Query su tabella condivisa
     */
    public static function getByProduct($productId)
    {
        return static::where('product_id', $productId)
            ->with('order')
            ->get();
    }
    
    /**
     * Ottiene il totale per ordine
     * 
     * Problema: Aggregazione su tabella condivisa
     */
    public static function getTotalForOrder($orderId)
    {
        return static::where('order_id', $orderId)
            ->sum('total');
    }
    
    /**
     * Ottiene la quantità totale per prodotto
     * 
     * Problema: Aggregazione su tabella condivisa
     */
    public static function getTotalQuantityForProduct($productId)
    {
        return static::where('product_id', $productId)
            ->sum('quantity');
    }
    
    /**
     * Ottiene i prodotti più venduti
     * 
     * Problema: Query complessa su database condiviso
     */
    public static function getTopSellingProducts($limit = 10)
    {
        return static::selectRaw('product_id, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Verifica se l'item può essere modificato
     * 
     * Problema: Verifica su multiple tabelle condivise
     */
    public function canBeModified()
    {
        return $this->order && in_array($this->order->status, ['pending', 'processing']);
    }
    
    /**
     * Modifica l'item
     * 
     * Problema: Modifica su multiple tabelle condivise
     */
    public function modify($quantity, $price)
    {
        if (!$this->canBeModified()) {
            throw new \Exception('Order item cannot be modified');
        }
        
        $this->quantity = $quantity;
        $this->price = $price;
        $this->total = $this->calculateTotal();
        $this->save();
        
        return $this;
    }
}
