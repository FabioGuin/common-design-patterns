<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeoutEvent extends Model
{
    protected $table = 'timeout_events';
    
    protected $fillable = [
        'service_name',
        'timeout_ms',
        'execution_time',
        'event_type',
        'error_message',
    ];

    protected $casts = [
        'timeout_ms' => 'integer',
        'execution_time' => 'float',
    ];

    public $timestamps = true;
}
