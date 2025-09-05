<?php

namespace App\DTOs\Article;

use App\DTOs\Base\BaseDTO;

class CreateArticleDTO extends BaseDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $userId,
        public readonly ?string $excerpt = null,
        public readonly string $status = 'draft'
    ) {
        $this->validate();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:50',
            'userId' => 'required|integer|exists:users,id',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published'
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Il titolo è obbligatorio',
            'title.min' => 'Il titolo deve essere di almeno 3 caratteri',
            'title.max' => 'Il titolo non può superare i 255 caratteri',
            'content.required' => 'Il contenuto è obbligatorio',
            'content.min' => 'Il contenuto deve essere di almeno 50 caratteri',
            'userId.required' => 'L\'autore è obbligatorio',
            'userId.exists' => 'L\'autore selezionato non esiste',
            'excerpt.max' => 'L\'excerpt non può superare i 500 caratteri',
            'status.required' => 'Lo stato è obbligatorio',
            'status.in' => 'Lo stato deve essere "draft" o "published"'
        ];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->userId,
            'excerpt' => $this->excerpt,
            'status' => $this->status
        ];
    }

    /**
     * Genera uno slug dal titolo
     */
    public function generateSlug(): string
    {
        return \Illuminate\Support\Str::slug($this->title);
    }

    /**
     * Genera un excerpt dal contenuto
     */
    public function generateExcerpt(int $length = 150): string
    {
        $excerpt = strip_tags($this->content);
        return \Illuminate\Support\Str::limit($excerpt, $length);
    }

    /**
     * Verifica se l'articolo è pronto per la pubblicazione
     */
    public function isReadyForPublishing(): bool
    {
        return $this->status === 'published' && 
               strlen($this->content) >= 100 && 
               strlen($this->title) >= 10;
    }

    /**
     * Ottiene i metadati dell'articolo
     */
    public function getMetadata(): array
    {
        return [
            'word_count' => str_word_count($this->content),
            'character_count' => strlen($this->content),
            'reading_time' => ceil(str_word_count($this->content) / 200), // 200 parole al minuto
            'slug' => $this->generateSlug(),
            'excerpt' => $this->excerpt ?? $this->generateExcerpt()
        ];
    }
}
