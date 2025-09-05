<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2'
    ];

    /**
     * Relazione con l'ordine
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relazione con il prodotto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcola il totale per questo elemento
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Scope per elementi di ordini completati
     */
    public function scopeFromCompletedOrders($query)
    {
        return $query->whereHas('order', function ($q) {
            $q->where('status', 'completed');
        });
    }

    /**
     * Scope per elementi di un prodotto specifico
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope per elementi di una categoria specifica
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->whereHas('product', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    /**
     * Calcola il totale delle vendite per prodotto
     */
    public static function getTotalSalesByProduct($productId)
    {
        return static::fromCompletedOrders()
            ->forProduct($productId)
            ->sum(\DB::raw('quantity * price'));
    }

    /**
     * Calcola il totale delle vendite per categoria
     */
    public static function getTotalSalesByCategory($categoryId)
    {
        return static::fromCompletedOrders()
            ->forCategory($categoryId)
            ->sum(\DB::raw('quantity * price'));
    }

    /**
     * Calcola la quantità totale venduta per prodotto
     */
    public static function getTotalQuantityByProduct($productId)
    {
        return static::fromCompletedOrders()
            ->forProduct($productId)
            ->sum('quantity');
    }

    /**
     * Ottiene le statistiche dell'elemento
     */
    public function getStats()
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name ?? 'N/A',
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total
        ];
    }
}
