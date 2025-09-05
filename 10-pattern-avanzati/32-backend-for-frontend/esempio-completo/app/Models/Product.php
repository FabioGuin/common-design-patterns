<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'category',
        'stock_quantity',
        'is_active',
        'sku',
        'weight',
        'dimensions',
        'sales_count',
        'rating'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'sales_count' => 'integer',
        'rating' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relazione con gli ordini
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'price', 'tax_rate'])
            ->withTimestamps();
    }

    /**
     * Relazione con gli item dell'ordine
     */
    public function orderItems()
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
     * Scope per prodotti per categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope per prodotti per prezzo
     */
    public function scopeByPriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope per prodotti in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope per ricerca
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Verifica se il prodotto è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }

    /**
     * Ottiene il prezzo formattato
     */
    public function getFormattedPriceAttribute(): string
    {
        return '€ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Ottiene le dimensioni formattate
     */
    public function getFormattedDimensionsAttribute(): string
    {
        if (!$this->dimensions) {
            return 'N/A';
        }

        $dims = json_decode($this->dimensions, true);
        if (!$dims) {
            return 'N/A';
        }

        return "{$dims['length']} x {$dims['width']} x {$dims['height']} cm";
    }

    /**
     * Ottiene il peso formattato
     */
    public function getFormattedWeightAttribute(): string
    {
        return $this->weight ? $this->weight . ' kg' : 'N/A';
    }

    /**
     * Ottiene il rating formattato
     */
    public function getFormattedRatingAttribute(): string
    {
        return $this->rating ? number_format($this->rating, 1) . '/5' : 'N/A';
    }

    /**
     * Ottiene statistiche del prodotto
     */
    public function getStats(): array
    {
        return [
            'total_orders' => $this->orders->count(),
            'total_quantity_sold' => $this->orders->sum('pivot.quantity'),
            'total_revenue' => $this->orders->sum(function ($order) {
                return $order->pivot->quantity * $order->pivot->price;
            }),
            'average_rating' => $this->rating,
            'is_available' => $this->isAvailable()
        ];
    }
}
