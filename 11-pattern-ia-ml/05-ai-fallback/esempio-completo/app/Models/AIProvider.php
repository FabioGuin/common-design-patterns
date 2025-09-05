<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIProvider extends Model
{
    protected $fillable = [
        'name',
        'status',
        'failure_count',
        'last_failure_at',
        'circuit_breaker_state',
        'retry_after',
        'success_rate'
    ];

    protected $casts = [
        'failure_count' => 'integer',
        'last_failure_at' => 'datetime',
        'retry_after' => 'datetime',
        'success_rate' => 'decimal:2'
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'failure_count' => $this->failure_count,
            'last_failure_at' => $this->last_failure_at?->toDateTimeString(),
            'circuit_breaker_state' => $this->circuit_breaker_state,
            'retry_after' => $this->retry_after?->toDateTimeString(),
            'success_rate' => $this->success_rate,
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
