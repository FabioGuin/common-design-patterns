<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Recupera tutti gli articoli
     */
    public function findAll(): Collection
    {
        return Article::with('user')->get();
    }

    /**
     * Recupera un articolo per ID
     */
    public function findById(int $id): ?Article
    {
        return Article::with('user')->find($id);
    }

    /**
     * Recupera articoli per autore
     */
    public function findByAuthor(int $authorId): Collection
    {
        return Article::where('user_id', $authorId)
                     ->with('user')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Recupera articoli pubblicati
     */
    public function findPublished(): Collection
    {
        return Article::where('status', 'published')
                     ->whereNotNull('published_at')
                     ->with('user')
                     ->orderBy('published_at', 'desc')
                     ->get();
    }

    /**
     * Recupera articoli in bozza
     */
    public function findDrafts(): Collection
    {
        return Article::where('status', 'draft')
                     ->with('user')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Cerca articoli per termine
     */
    public function search(string $term): Collection
    {
        return Article::where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
        })
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Recupera articoli recenti
     */
    public function findRecent(int $limit = 10): Collection
    {
        return Article::with('user')
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Recupera articoli per categoria (se implementata)
     */
    public function findByCategory(string $category): Collection
    {
        // Implementazione base - può essere estesa per supportare categorie
        return Article::where('status', 'published')
                     ->with('user')
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Recupera articoli con paginazione
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Article::with('user')
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    /**
     * Conta il numero totale di articoli
     */
    public function count(): int
    {
        return Article::count();
    }

    /**
     * Conta articoli pubblicati
     */
    public function countPublished(): int
    {
        return Article::where('status', 'published')
                     ->whereNotNull('published_at')
                     ->count();
    }

    /**
     * Conta articoli in bozza
     */
    public function countDrafts(): int
    {
        return Article::where('status', 'draft')->count();
    }

    /**
     * Crea un nuovo articolo
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function update(int $id, array $data): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }

        return $article->update($data);
    }

    /**
     * Elimina un articolo
     */
    public function delete(int $id): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }

        return $article->delete();
    }

    /**
     * Pubblica un articolo
     */
    public function publish(int $id): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }

        return $article->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Mette in bozza un articolo
     */
    public function draft(int $id): bool
    {
        $article = $this->findById($id);
        if (!$article) {
            return false;
        }

        return $article->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Recupera articoli con statistiche
     */
    public function findWithStats(): Collection
    {
        return Article::with(['user'])
                     ->withCount(['comments as comments_count'])
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Recupera articoli più popolari
     */
    public function findPopular(int $limit = 5): Collection
    {
        return Article::where('status', 'published')
                     ->with('user')
                     ->orderBy('views_count', 'desc')
                     ->limit($limit)
                     ->get();
    }

    /**
     * Recupera articoli correlati
     */
    public function findRelated(int $articleId, int $limit = 5): Collection
    {
        $article = $this->findById($articleId);
        if (!$article) {
            return collect();
        }

        return Article::where('id', '!=', $articleId)
                     ->where('user_id', $article->user_id)
                     ->where('status', 'published')
                     ->with('user')
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }
}
