<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimit extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'requests_count',
        'window_start',
        'limit',
        'remaining',
        'reset_at'
    ];

    protected $casts = [
        'requests_count' => 'integer',
        'window_start' => 'datetime',
        'limit' => 'integer',
        'remaining' => 'integer',
        'reset_at' => 'datetime'
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'endpoint' => $this->endpoint,
            'requests_count' => $this->requests_count,
            'window_start' => $this->window_start?->toDateTimeString(),
            'limit' => $this->limit,
            'remaining' => $this->remaining,
            'reset_at' => $this->reset_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
