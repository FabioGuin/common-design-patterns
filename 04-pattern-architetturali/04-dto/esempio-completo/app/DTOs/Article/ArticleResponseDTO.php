<?php

namespace App\DTOs\Article;

use App\DTOs\Base\BaseDTO;
use App\Models\Article;

class ArticleResponseDTO extends BaseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $excerpt,
        public readonly string $status,
        public readonly string $slug,
        public readonly string $authorName,
        public readonly string $authorEmail,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $publishedAt = null
    ) {}

    public static function fromModel(Article $article): self
    {
        return new self(
            id: $article->id,
            title: $article->title,
            content: $article->content,
            excerpt: $article->excerpt,
            status: $article->status,
            slug: $article->slug,
            authorName: $article->user->name,
            authorEmail: $article->user->email,
            createdAt: $article->created_at->format('Y-m-d H:i:s'),
            updatedAt: $article->updated_at->format('Y-m-d H:i:s'),
            publishedAt: $article->published_at?->format('Y-m-d H:i:s')
        );
    }

    protected function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|min:1',
            'content' => 'required|string|min:1',
            'excerpt' => 'required|string|min:1',
            'status' => 'required|in:draft,published',
            'slug' => 'required|string|min:1',
            'authorName' => 'required|string|min:1',
            'authorEmail' => 'required|email',
            'createdAt' => 'required|string',
            'updatedAt' => 'required|string',
            'publishedAt' => 'nullable|string'
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'slug' => $this->slug,
            'author' => [
                'name' => $this->authorName,
                'email' => $this->authorEmail
            ],
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'published_at' => $this->publishedAt
        ];
    }

    /**
     * Ottiene i metadati dell'articolo
     */
    public function getMetadata(): array
    {
        return [
            'word_count' => str_word_count($this->content),
            'character_count' => strlen($this->content),
            'reading_time' => ceil(str_word_count($this->content) / 200),
            'is_published' => $this->status === 'published',
            'days_since_creation' => now()->diffInDays($this->createdAt),
            'days_since_update' => now()->diffInDays($this->updatedAt)
        ];
    }

    /**
     * Ottiene l'URL dell'articolo
     */
    public function getUrl(): string
    {
        return route('articles.show', $this->slug);
    }

    /**
     * Ottiene l'URL di modifica dell'articolo
     */
    public function getEditUrl(): string
    {
        return route('articles.edit', $this->id);
    }

    /**
     * Verifica se l'articolo è pubblicato
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Verifica se l'articolo è in bozza
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Ottiene il tempo di lettura stimato
     */
    public function getReadingTime(): int
    {
        return ceil(str_word_count($this->content) / 200);
    }

    /**
     * Ottiene un excerpt più lungo
     */
    public function getLongExcerpt(int $length = 300): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), $length);
    }

    /**
     * Ottiene un excerpt più corto
     */
    public function getShortExcerpt(int $length = 100): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($this->content), $length);
    }
}
