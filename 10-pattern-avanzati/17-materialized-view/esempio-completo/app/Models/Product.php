<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock_quantity',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Relazione con la categoria
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relazione con gli elementi degli ordini
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope per prodotti attivi
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per prodotti in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Calcola il totale delle vendite per questo prodotto
     */
    public function getTotalSalesAttribute()
    {
        return $this->orderItems()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum(\DB::raw('order_items.quantity * order_items.price'));
    }

    /**
     * Calcola la quantità totale venduta
     */
    public function getTotalQuantitySoldAttribute()
    {
        return $this->orderItems()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');
    }

    /**
     * Ottiene le statistiche del prodotto
     */
    public function getStats()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category->name ?? 'N/A',
            'price' => $this->price,
            'stock' => $this->stock_quantity,
            'total_sales' => $this->total_sales,
            'total_quantity_sold' => $this->total_quantity_sold,
            'is_active' => $this->is_active
        ];
    }
}
