<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $connection = 'payment_service';
    protected $table = 'payments';
    
    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'processed_at',
        'refunded_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public $timestamps = true;
}
