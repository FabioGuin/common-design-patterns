<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relazione con i prodotti
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope per categorie attive
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calcola il totale delle vendite per questa categoria
     */
    public function getTotalSalesAttribute()
    {
        return $this->products()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum(\DB::raw('order_items.quantity * order_items.price'));
    }

    /**
     * Calcola il numero di prodotti venduti in questa categoria
     */
    public function getTotalProductsSoldAttribute()
    {
        return $this->products()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');
    }

    /**
     * Calcola il numero di ordini per questa categoria
     */
    public function getTotalOrdersAttribute()
    {
        return $this->products()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->distinct('orders.id')
            ->count('orders.id');
    }

    /**
     * Ottiene le statistiche della categoria
     */
    public function getStats()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'total_sales' => $this->total_sales,
            'total_products_sold' => $this->total_products_sold,
            'total_orders' => $this->total_orders,
            'is_active' => $this->is_active
        ];
    }
}
