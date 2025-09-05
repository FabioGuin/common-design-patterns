<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
        private NotificationService $notificationService,
        private ValidationService $validationService
    ) {}

    /**
     * Crea un nuovo articolo
     */
    public function createArticle(array $data): Article
    {
        // Validazione business
        $this->validationService->validateArticleData($data);
        
        // Processamento dati
        $processedData = $this->processArticleData($data);
        
        // Creazione articolo
        $article = $this->articleRepository->create($processedData);
        
        // Azioni post-creazione
        $this->notificationService->notifyArticleCreated($article);
        
        return $article;
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function updateArticle(int $id, array $data): Article
    {
        $article = $this->articleRepository->findById($id);
        if (!$article) {
            throw new \Exception('Articolo non trovato');
        }

        // Validazione business
        $this->validationService->validateArticleData($data, $id);
        
        // Processamento dati
        $processedData = $this->processArticleData($data);
        
        // Aggiornamento articolo
        $this->articleRepository->update($id, $processedData);
        
        // Azioni post-aggiornamento
        $this->notificationService->notifyArticleUpdated($article);
        
        return $article->fresh();
    }

    /**
     * Pubblica un articolo
     */
    public function publishArticle(int $id): Article
    {
        $article = $this->articleRepository->findById($id);
        if (!$article) {
            throw new \Exception('Articolo non trovato');
        }

        // Validazione business per pubblicazione
        $this->validateArticleForPublishing($article);
        
        // Pubblicazione
        $this->articleRepository->publish($id);
        
        // Azioni post-pubblicazione
        $this->notificationService->notifyArticlePublished($article);
        
        return $article->fresh();
    }

    /**
     * Mette in bozza un articolo
     */
    public function draftArticle(int $id): Article
    {
        $article = $this->articleRepository->findById($id);
        if (!$article) {
            throw new \Exception('Articolo non trovato');
        }

        // Mette in bozza
        $this->articleRepository->draft($id);
        
        // Azioni post-bozza
        $this->notificationService->notifyArticleDrafted($article);
        
        return $article->fresh();
    }

    /**
     * Elimina un articolo
     */
    public function deleteArticle(int $id): bool
    {
        $article = $this->articleRepository->findById($id);
        if (!$article) {
            throw new \Exception('Articolo non trovato');
        }

        // Azioni pre-eliminazione
        $this->notificationService->notifyArticleDeleted($article);
        
        // Eliminazione
        return $this->articleRepository->delete($id);
    }

    /**
     * Recupera articoli con filtri
     */
    public function getArticles(array $filters = []): Collection
    {
        $query = $this->articleRepository->findAll();

        // Applica filtri
        if (isset($filters['status'])) {
            $query = $query->where('status', $filters['status']);
        }

        if (isset($filters['author_id'])) {
            $query = $query->where('user_id', $filters['author_id']);
        }

        if (isset($filters['search'])) {
            $query = $this->articleRepository->search($filters['search']);
        }

        return $query;
    }

    /**
     * Recupera un articolo per ID
     */
    public function getArticleById(int $id): Article
    {
        $article = $this->articleRepository->findById($id);
        if (!$article) {
            throw new \Exception('Articolo non trovato');
        }

        return $article;
    }

    /**
     * Recupera articoli popolari
     */
    public function getPopularArticles(int $limit = 10): Collection
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
     * Processa i dati dell'articolo
     */
    private function processArticleData(array $data): array
    {
        // Genera slug
        if (isset($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Genera excerpt se non fornito
        if (isset($data['content']) && empty($data['excerpt'])) {
            $data['excerpt'] = $this->generateExcerpt($data['content']);
        }

        // Pulisce il contenuto
        if (isset($data['content'])) {
            $data['content'] = $this->sanitizeContent($data['content']);
        }

        // Imposta data di pubblicazione se necessario
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Rimuove data di pubblicazione se in bozza
        if (isset($data['status']) && $data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        return $data;
    }

    /**
     * Genera uno slug unico
     */
    private function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verifica se uno slug esiste
     */
    private function slugExists(string $slug): bool
    {
        return Article::where('slug', $slug)->exists();
    }

    /**
     * Genera un excerpt
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
        $allowedTags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img>';
        return strip_tags($content, $allowedTags);
    }

    /**
     * Valida un articolo per la pubblicazione
     */
    private function validateArticleForPublishing(Article $article): void
    {
        if (empty($article->title)) {
            throw new \Exception('Il titolo è obbligatorio per pubblicare');
        }

        if (empty($article->content)) {
            throw new \Exception('Il contenuto è obbligatorio per pubblicare');
        }

        if (strlen($article->content) < 100) {
            throw new \Exception('Il contenuto deve essere di almeno 100 caratteri per pubblicare');
        }
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
}
