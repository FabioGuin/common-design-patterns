<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoredEvent extends Model
{
    protected $table = 'stored_events';
    
    protected $fillable = [
        'aggregate_id',
        'event_type',
        'event_data',
        'version',
    ];

    protected $casts = [
        'event_data' => 'array',
        'version' => 'integer',
    ];

    public $timestamps = true;
}
