<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saga extends Model
{
    protected $table = 'sagas';
    
    protected $fillable = [
        'type',
        'status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public $timestamps = true;

    public function steps()
    {
        return $this->hasMany(SagaStep::class);
    }
}
