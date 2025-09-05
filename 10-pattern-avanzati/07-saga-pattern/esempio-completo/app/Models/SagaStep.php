<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SagaStep extends Model
{
    protected $table = 'saga_steps';
    
    protected $fillable = [
        'saga_id',
        'step_name',
        'status',
        'data',
        'executed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'executed_at' => 'datetime',
    ];

    public $timestamps = true;

    public function saga()
    {
        return $this->belongsTo(Saga::class);
    }
}
