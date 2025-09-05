<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProjection extends Model
{
    protected $table = 'order_projections';
    
    protected $fillable = [
        'order_id',
        'customer_id',
        'items',
        'total_amount',
        'shipping_address',
        'status',
        'payment_method',
        'transaction_id',
        'tracking_number',
        'carrier',
        'delivery_confirmation',
        'cancellation_reason',
        'refund_amount',
        'refund_reason',
        'version',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'version' => 'integer',
    ];

    public $timestamps = true;
}
