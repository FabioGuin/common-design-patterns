<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShardingEvent extends Model
{
    protected $table = 'sharding_events';
    
    protected $fillable = [
        'entity',
        'shard',
        'key',
        'event_type',
        'error_message',
    ];

    public $timestamps = true;
}
