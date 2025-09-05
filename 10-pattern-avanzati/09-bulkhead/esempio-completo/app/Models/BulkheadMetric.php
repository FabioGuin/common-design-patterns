<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkheadMetric extends Model
{
    protected $table = 'bulkhead_metrics';
    
    protected $fillable = [
        'service_name',
        'metric_type',
        'execution_time',
        'memory_used',
        'active_threads',
        'active_connections',
        'queue_length',
        'total_executions',
        'successful_executions',
        'failed_executions',
    ];

    protected $casts = [
        'execution_time' => 'float',
        'memory_used' => 'integer',
        'active_threads' => 'integer',
        'active_connections' => 'integer',
        'queue_length' => 'integer',
        'total_executions' => 'integer',
        'successful_executions' => 'integer',
        'failed_executions' => 'integer',
    ];

    public $timestamps = true;
}
