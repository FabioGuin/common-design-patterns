<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThrottlingMetric extends Model
{
    protected $table = 'throttling_metrics';
    
    protected $fillable = [
        'service_name',
        'identifier',
        'endpoint',
        'rate_limit',
        'window_seconds',
        'execution_time',
        'memory_used',
        'total_requests',
        'successful_requests',
        'throttled_requests',
        'error_requests',
    ];

    protected $casts = [
        'rate_limit' => 'integer',
        'window_seconds' => 'integer',
        'execution_time' => 'float',
        'memory_used' => 'integer',
        'total_requests' => 'integer',
        'successful_requests' => 'integer',
        'throttled_requests' => 'integer',
        'error_requests' => 'integer',
    ];

    public $timestamps = true;
}
