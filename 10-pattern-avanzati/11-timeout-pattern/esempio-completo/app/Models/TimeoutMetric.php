<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeoutMetric extends Model
{
    protected $table = 'timeout_metrics';
    
    protected $fillable = [
        'service_name',
        'timeout_ms',
        'execution_time',
        'memory_used',
        'total_operations',
        'successful_operations',
        'timeout_operations',
        'error_operations',
    ];

    protected $casts = [
        'timeout_ms' => 'integer',
        'execution_time' => 'float',
        'memory_used' => 'integer',
        'total_operations' => 'integer',
        'successful_operations' => 'integer',
        'timeout_operations' => 'integer',
        'error_operations' => 'integer',
    ];

    public $timestamps = true;
}
