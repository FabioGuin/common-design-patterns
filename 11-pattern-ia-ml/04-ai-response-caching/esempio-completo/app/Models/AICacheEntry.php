<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AICacheEntry extends Model
{
    protected $fillable = [
        'query_hash',
        'query',
        'response',
        'provider',
        'tokens_used',
        'cost',
        'hit_count',
        'expires_at'
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'hit_count' => 'integer',
        'expires_at' => 'datetime'
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'query_hash' => $this->query_hash,
            'query' => $this->query,
            'response' => $this->response,
            'provider' => $this->provider,
            'tokens_used' => $this->tokens_used,
            'cost' => $this->cost,
            'hit_count' => $this->hit_count,
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
