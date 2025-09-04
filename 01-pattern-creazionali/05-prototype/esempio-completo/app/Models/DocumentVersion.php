<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'version_name',
        'content',
        'metadata',
        'settings',
        'created_by',
        'change_description'
    ];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restore(): Document
    {
        $this->document->update([
            'content' => $this->content,
            'metadata' => $this->metadata,
            'settings' => $this->settings,
        ]);

        return $this->document;
    }
}
