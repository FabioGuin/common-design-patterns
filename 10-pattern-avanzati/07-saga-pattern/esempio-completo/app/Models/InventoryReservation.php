<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
{
    protected $table = 'inventory_reservations';
    
    protected $fillable = [
        'reservation_id',
        'product_id',
        'quantity',
        'order_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime',
    ];

    public $timestamps = true;
}
