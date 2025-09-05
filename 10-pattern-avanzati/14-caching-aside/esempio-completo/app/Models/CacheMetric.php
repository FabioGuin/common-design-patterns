<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheMetric extends Model
{
    protected $table = 'cache_metrics';
    
    protected $fillable = [
        'entity',
        'key',
        'operation',
        'execution_time',
        'memory_used',
        'total_operations',
        'cache_hits',
        'cache_misses',
        'cache_errors',
    ];

    protected $casts = [
        'execution_time' => 'float',
        'memory_used' => 'integer',
        'total_operations' => 'integer',
        'cache_hits' => 'integer',
        'cache_misses' => 'integer',
        'cache_errors' => 'integer',
    ];

    public $timestamps = true;
}
