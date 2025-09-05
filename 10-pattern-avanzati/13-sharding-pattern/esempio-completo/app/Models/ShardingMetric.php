<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShardingMetric extends Model
{
    protected $table = 'sharding_metrics';
    
    protected $fillable = [
        'entity',
        'shard',
        'key',
        'execution_time',
        'memory_used',
        'total_queries',
        'successful_queries',
        'failed_queries',
    ];

    protected $casts = [
        'execution_time' => 'float',
        'memory_used' => 'integer',
        'total_queries' => 'integer',
        'successful_queries' => 'integer',
        'failed_queries' => 'integer',
    ];

    public $timestamps = true;
}
