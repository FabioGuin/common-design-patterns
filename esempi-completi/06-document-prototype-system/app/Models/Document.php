<?php

namespace App\Models;

use App\Traits\Cloneable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory, Cloneable;

    protected $fillable = [
        'title',
        'content',
        'template_id',
        'status',
        'metadata',
        'settings',
        'tags',
        'author_id',
        'version',
        'parent_id'
    ];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'array',
        'tags' => 'array',
        'version' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function metadata()
    {
        return $this->hasMany(DocumentMetadata::class);
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public function cloneWithCustomData(array $customData = []): self
    {
        $clone = $this->clone();
        
        // Applica i dati personalizzati
        foreach ($customData as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $clone->$key = $value;
            }
        }
        
        return $clone;
    }

    public function createVersion(string $versionName = null): DocumentVersion
    {
        return $this->versions()->create([
            'version_name' => $versionName ?? 'v' . ($this->version + 1),
            'content' => $this->content,
            'metadata' => $this->metadata,
            'settings' => $this->settings,
            'created_by' => auth()->id(),
        ]);
    }

    public function getFullTitleAttribute(): string
    {
        return $this->title . ' (v' . $this->version . ')';
    }

    public function isTemplate(): bool
    {
        return $this->template_id !== null;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function publish(): void
    {
        $this->update(['status' => 'published']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMetadataValue(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    public function getSettingValue(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSettingValue(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }
}
