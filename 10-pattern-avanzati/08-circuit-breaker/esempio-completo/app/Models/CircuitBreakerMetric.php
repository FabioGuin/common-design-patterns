<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CircuitBreakerMetric extends Model
{
    protected $table = 'circuit_breaker_metrics';
    
    protected $fillable = [
        'service_name',
        'metric_type',
        'state',
        'failure_count',
        'success_count',
        'total_calls',
        'total_failures',
    ];

    protected $casts = [
        'failure_count' => 'integer',
        'success_count' => 'integer',
        'total_calls' => 'integer',
        'total_failures' => 'integer',
    ];

    public $timestamps = true;
}
