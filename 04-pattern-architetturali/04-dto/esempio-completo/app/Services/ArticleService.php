<?php

namespace App\Services;

use App\Models\Article;
use App\DTOs\Article\CreateArticleDTO;
use App\DTOs\Article\UpdateArticleDTO;
use App\DTOs\Article\ArticleResponseDTO;
use App\DTOs\Article\ArticleListDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    /**
     * Crea un nuovo articolo
     */
    public function createArticle(CreateArticleDTO $dto): ArticleResponseDTO
    {
        $article = Article::create($dto->toArray());
        $article->load('user');
        
        return ArticleResponseDTO::fromModel($article);
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function updateArticle(int $id, UpdateArticleDTO $dto): ArticleResponseDTO
    {
        $article = Article::findOrFail($id);
        $article->update($dto->toArray());
        $article->load('user');
        
        return ArticleResponseDTO::fromModel($article->fresh());
    }

    /**
     * Recupera un articolo per ID
     */
    public function getArticle(int $id): ArticleResponseDTO
    {
        $article = Article::with('user')->findOrFail($id);
        
        return ArticleResponseDTO::fromModel($article);
    }

    /**
     * Recupera un articolo per slug
     */
    public function getArticleBySlug(string $slug): ArticleResponseDTO
    {
        $article = Article::with('user')->where('slug', $slug)->firstOrFail();
        
        return ArticleResponseDTO::fromModel($article);
    }

    /**
     * Recupera tutti gli articoli
     */
    public function getAllArticles(): Collection
    {
        return Article::with('user')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Recupera articoli con filtri
     */
    public function getArticles(array $filters = []): Collection
    {
        $query = Article::with('user');

        // Filtro per stato
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtro per autore
        if (isset($filters['author_id'])) {
            $query->where('user_id', $filters['author_id']);
        }

        // Filtro per ricerca
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('excerpt', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filtro per data
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Ordinamento
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->get();
    }

    /**
     * Recupera articoli pubblicati
     */
    public function getPublishedArticles(): Collection
    {
        return Article::with('user')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Recupera articoli in bozza
     */
    public function getDraftArticles(): Collection
    {
        return Article::with('user')
            ->where('status', 'draft')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Recupera articoli popolari
     */
    public function getPopularArticles(int $limit = 10): Collection
    {
        return Article::with('user')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupera articoli correlati
     */
    public function getRelatedArticles(int $articleId, int $limit = 5): Collection
    {
        $article = Article::findOrFail($articleId);
        
        return Article::with('user')
            ->where('id', '!=', $articleId)
            ->where('status', 'published')
            ->where(function ($query) use ($article) {
                $query->where('title', 'like', '%' . $article->title . '%')
                      ->orWhere('content', 'like', '%' . $article->title . '%');
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Pubblica un articolo
     */
    public function publishArticle(int $id): ArticleResponseDTO
    {
        $article = Article::findOrFail($id);
        
        if ($article->status === 'published') {
            throw new \Exception('L\'articolo è già pubblicato');
        }

        $article->update([
            'status' => 'published',
            'published_at' => now()
        ]);
        
        $article->load('user');
        
        return ArticleResponseDTO::fromModel($article->fresh());
    }

    /**
     * Mette in bozza un articolo
     */
    public function draftArticle(int $id): ArticleResponseDTO
    {
        $article = Article::findOrFail($id);
        
        if ($article->status === 'draft') {
            throw new \Exception('L\'articolo è già in bozza');
        }

        $article->update([
            'status' => 'draft',
            'published_at' => null
        ]);
        
        $article->load('user');
        
        return ArticleResponseDTO::fromModel($article->fresh());
    }

    /**
     * Elimina un articolo
     */
    public function deleteArticle(int $id): bool
    {
        $article = Article::findOrFail($id);
        
        return $article->delete();
    }

    /**
     * Recupera statistiche degli articoli
     */
    public function getArticleStats(): array
    {
        return [
            'total' => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'drafts' => Article::where('status', 'draft')->count(),
            'this_month' => Article::whereMonth('created_at', now()->month)->count(),
            'this_year' => Article::whereYear('created_at', now()->year)->count()
        ];
    }

    /**
     * Recupera articoli per pagina
     */
    public function getArticlesPaginated(array $filters = [], int $perPage = 15)
    {
        $query = Article::with('user');

        // Applica filtri
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['author_id'])) {
            $query->where('user_id', $filters['author_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Ordinamento
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Cerca articoli
     */
    public function searchArticles(string $term, int $limit = 20): Collection
    {
        return Article::with('user')
            ->where('status', 'published')
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', '%' . $term . '%')
                      ->orWhere('content', 'like', '%' . $term . '%')
                      ->orWhere('excerpt', 'like', '%' . $term . '%');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupera articoli recenti
     */
    public function getRecentArticles(int $limit = 5): Collection
    {
        return Article::with('user')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Recupera articoli per autore
     */
    public function getArticlesByAuthor(int $authorId): Collection
    {
        return Article::with('user')
            ->where('user_id', $authorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
