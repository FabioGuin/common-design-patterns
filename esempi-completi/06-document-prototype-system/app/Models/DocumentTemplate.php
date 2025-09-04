<?php

namespace App\Models;

use App\Traits\Cloneable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory, Cloneable;

    protected $fillable = [
        'name',
        'description',
        'content',
        'metadata',
        'settings',
        'tags',
        'category',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createDocument(string $title, array $customData = []): Document
    {
        $document = new Document([
            'title' => $title,
            'content' => $this->content,
            'template_id' => $this->id,
            'status' => 'draft',
            'metadata' => $this->metadata,
            'settings' => $this->settings,
            'tags' => $this->tags,
            'version' => 1,
        ]);

        // Applica i dati personalizzati
        foreach ($customData as $key => $value) {
            if (in_array($key, $document->getFillable())) {
                $document->$key = $value;
            }
        }

        $document->save();
        return $document;
    }

    public function cloneTemplate(string $newName = null): self
    {
        $clone = $this->clone();
        
        if ($newName) {
            $clone->name = $newName;
        } else {
            $clone->name = 'Copia di ' . $this->name;
        }
        
        $clone->is_active = false; // I template clonati sono inattivi di default
        
        return $clone;
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function getUsageCount(): int
    {
        return $this->documents()->count();
    }

    public function getLatestDocument(): ?Document
    {
        return $this->documents()->latest()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
