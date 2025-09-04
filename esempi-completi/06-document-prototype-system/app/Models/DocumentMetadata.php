<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMetadata extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function getValueAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }
}
