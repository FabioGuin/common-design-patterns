<?php

namespace App\QueryModels;

use Illuminate\Database\Eloquent\Model;

class OrderView extends Model
{
    protected $connection = 'mysql_read';
    protected $table = 'order_views';
    
    protected $fillable = [
        'id',
        'user_id',
        'items',
        'total_amount',
        'shipping_address',
        'billing_address',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'items' => 'array',
    ];

    public $timestamps = true;

    public function getItemsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }

    // Scope per query ottimizzate
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeOrderByTotal($query, string $direction = 'desc')
    {
        return $query->orderBy('total_amount', $direction);
    }
}
