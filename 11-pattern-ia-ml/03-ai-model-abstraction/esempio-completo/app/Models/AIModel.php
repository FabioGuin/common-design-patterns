<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIModel extends Model
{
    protected $fillable = [
        'name',
        'type',
        'provider',
        'performance_score',
        'cost_per_token',
        'max_tokens',
        'is_available'
    ];

    protected $casts = [
        'performance_score' => 'decimal:2',
        'cost_per_token' => 'decimal:6',
        'max_tokens' => 'integer',
        'is_available' => 'boolean'
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'provider' => $this->provider,
            'performance_score' => $this->performance_score,
            'cost_per_token' => $this->cost_per_token,
            'max_tokens' => $this->max_tokens,
            'is_available' => $this->is_available,
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
