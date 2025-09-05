<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThrottlingEvent extends Model
{
    protected $table = 'throttling_events';
    
    protected $fillable = [
        'service_name',
        'identifier',
        'endpoint',
        'event_type',
        'rate_limit',
        'window_seconds',
        'error_message',
    ];

    protected $casts = [
        'rate_limit' => 'integer',
        'window_seconds' => 'integer',
    ];

    public $timestamps = true;
}
