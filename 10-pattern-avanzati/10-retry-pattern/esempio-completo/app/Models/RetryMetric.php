<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetryMetric extends Model
{
    protected $table = 'retry_metrics';
    
    protected $fillable = [
        'service_name',
        'total_attempts',
        'successful_attempts',
        'failed_attempts',
        'execution_time',
        'memory_used',
    ];

    protected $casts = [
        'total_attempts' => 'integer',
        'successful_attempts' => 'integer',
        'failed_attempts' => 'integer',
        'execution_time' => 'float',
        'memory_used' => 'integer',
    ];

    public $timestamps = true;
}
