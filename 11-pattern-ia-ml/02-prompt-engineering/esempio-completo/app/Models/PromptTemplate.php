<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'template',
        'variables',
        'description',
        'performance_score',
        'usage_count'
    ];

    protected $casts = [
        'variables' => 'array',
        'performance_score' => 'decimal:2',
        'usage_count' => 'integer'
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'template' => $this->template,
            'variables' => $this->variables,
            'description' => $this->description,
            'performance_score' => $this->performance_score,
            'usage_count' => $this->usage_count,
            'created_at' => $this->created_at?->toDateTimeString()
        ];
    }
}
