<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    
    protected $fillable = [
        'notification_id',
        'order_id',
        'type',
        'recipient',
        'status',
        'sent_at',
        'cancelled_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public $timestamps = true;
}
