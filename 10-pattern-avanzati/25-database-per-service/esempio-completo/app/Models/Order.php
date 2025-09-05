<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'order_service';
    protected $table = 'orders';
    
    protected $fillable = [
        'user_id',
        'items',
        'total',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public $timestamps = true;
}
