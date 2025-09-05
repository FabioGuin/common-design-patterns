<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheEvent extends Model
{
    protected $table = 'cache_events';
    
    protected $fillable = [
        'entity',
        'key',
        'event_type',
        'error_message',
    ];

    public $timestamps = true;
}
