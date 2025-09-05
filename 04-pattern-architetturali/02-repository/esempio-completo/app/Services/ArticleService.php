<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Recupera tutti gli articoli
     */
    public function getAllArticles(): Collection
    {
        return $this->articleRepository->findAll();
    }

    /**
     * Recupera articoli pubblicati
     */
    public function getPublishedArticles(): Collection
    {
        return $this->articleRepository->findPublished();
    }

    /**
     * Recupera articoli in bozza
     */
    public function getDraftArticles(): Collection
    {
        return $this->articleRepository->findDrafts();
    }

    /**
     * Recupera un articolo per ID
     */
    public function getArticleById(int $id): ?Article
    {
        return $this->articleRepository->findById($id);
    }

    /**
     * Recupera articoli per autore
     */
    public function getArticlesByAuthor(int $authorId): Collection
    {
        return $this->articleRepository->findByAuthor($authorId);
    }

    /**
     * Cerca articoli per termine
     */
    public function searchArticles(string $term): Collection
    {
        if (empty(trim($term))) {
            return collect();
        }

        return $this->articleRepository->search($term);
    }

    /**
     * Recupera articoli recenti
     */
    public function getRecentArticles(int $limit = 10): Collection
    {
        return $this->articleRepository->findRecent($limit);
    }

    /**
     * Recupera articoli più popolari
     */
    public function getPopularArticles(int $limit = 5): Collection
    {
        return $this->articleRepository->findPopular($limit);
    }

    /**
     * Recupera articoli correlati
     */
    public function getRelatedArticles(int $articleId, int $limit = 5): Collection
    {
        return $this->articleRepository->findRelated($articleId, $limit);
    }

    /**
     * Crea un nuovo articolo
     */
    public function createArticle(array $data): Article
    {
        // Logica di business per la creazione
        $data = $this->prepareArticleData($data);

        return $this->articleRepository->create($data);
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function updateArticle(int $id, array $data): bool
    {
        // Logica di business per l'aggiornamento
        $data = $this->prepareArticleData($data);

        return $this->articleRepository->update($id, $data);
    }

    /**
     * Elimina un articolo
     */
    public function deleteArticle(int $id): bool
    {
        return $this->articleRepository->delete($id);
    }

    /**
     * Pubblica un articolo
     */
    public function publishArticle(int $id): bool
    {
        return $this->articleRepository->publish($id);
    }

    /**
     * Mette in bozza un articolo
     */
    public function draftArticle(int $id): bool
    {
        return $this->articleRepository->draft($id);
    }

    /**
     * Recupera statistiche degli articoli
     */
    public function getArticleStats(): array
    {
        return [
            'total' => $this->articleRepository->count(),
            'published' => $this->articleRepository->countPublished(),
            'drafts' => $this->articleRepository->countDrafts(),
        ];
    }

    /**
     * Recupera articoli con paginazione
     */
    public function getPaginatedArticles(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->articleRepository->paginate($perPage);
    }

    /**
     * Recupera articoli con statistiche
     */
    public function getArticlesWithStats(): Collection
    {
        return $this->articleRepository->findWithStats();
    }

    /**
     * Prepara i dati dell'articolo per il salvataggio
     */
    private function prepareArticleData(array $data): array
    {
        // Genera slug dal titolo
        if (isset($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Genera excerpt se non fornito
        if (isset($data['content']) && empty($data['excerpt'])) {
            $data['excerpt'] = $this->generateExcerpt($data['content']);
        }

        // Pulisce il contenuto HTML
        if (isset($data['content'])) {
            $data['content'] = $this->sanitizeContent($data['content']);
        }

        // Imposta data di pubblicazione se l'articolo è pubblicato
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Rimuove data di pubblicazione se l'articolo è in bozza
        if (isset($data['status']) && $data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        return $data;
    }

    /**
     * Genera uno slug unico dal titolo
     */
    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        // Verifica che lo slug sia unico
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verifica se uno slug esiste già
     */
    private function slugExists(string $slug): bool
    {
        return Article::where('slug', $slug)->exists();
    }

    /**
     * Genera un excerpt dal contenuto
     */
    private function generateExcerpt(string $content, int $length = 150): string
    {
        $excerpt = strip_tags($content);
        return Str::limit($excerpt, $length);
    }

    /**
     * Pulisce il contenuto HTML
     */
    private function sanitizeContent(string $content): string
    {
        // Permette solo tag HTML sicuri
        $allowedTags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img>';
        return strip_tags($content, $allowedTags);
    }

    /**
     * Valida i dati dell'articolo
     */
    public function validateArticleData(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Il titolo è obbligatorio';
        } elseif (strlen($data['title']) < 3) {
            $errors[] = 'Il titolo deve essere di almeno 3 caratteri';
        } elseif (strlen($data['title']) > 255) {
            $errors[] = 'Il titolo non può superare i 255 caratteri';
        }

        if (empty($data['content'])) {
            $errors[] = 'Il contenuto è obbligatorio';
        } elseif (strlen($data['content']) < 50) {
            $errors[] = 'Il contenuto deve essere di almeno 50 caratteri';
        }

        if (empty($data['user_id'])) {
            $errors[] = 'L\'autore è obbligatorio';
        }

        if (!in_array($data['status'] ?? '', ['draft', 'published'])) {
            $errors[] = 'Lo stato deve essere "draft" o "published"';
        }

        return $errors;
    }
}
