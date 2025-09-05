<?php

namespace App\QueryModels;

use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    protected $connection = 'mysql_read';
    protected $table = 'product_views';
    
    protected $fillable = [
        'id',
        'name',
        'description',
        'price',
        'stock',
        'category',
        'attributes',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_available' => 'boolean',
        'attributes' => 'array',
    ];

    public $timestamps = true;

    public function getAttributesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = json_encode($value);
    }

    // Scope per query ottimizzate
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopePriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
