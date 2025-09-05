<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIRequest extends Model
{
    protected $fillable = [
        'provider',
        'prompt',
        'response',
        'tokens_used',
        'cost',
        'response_time',
        'status',
        'error_message'
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:4',
        'response_time' => 'integer',
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'prompt' => $this->prompt,
            'response' => $this->response,
            'tokens_used' => $this->tokens_used,
            'cost' => $this->cost,
            'response_time' => $this->response_time,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
