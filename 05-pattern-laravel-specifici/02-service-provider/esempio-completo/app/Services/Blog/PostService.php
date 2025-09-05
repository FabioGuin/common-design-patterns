<?php

namespace App\Services\Blog;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostService
{
    protected int $cacheTtl;
    protected int $perPage;

    public function __construct(int $cacheTtl = 3600, int $perPage = 15)
    {
        $this->cacheTtl = $cacheTtl;
        $this->perPage = $perPage;
    }

    /**
     * Ottieni tutti i post con paginazione
     */
    public function getAllPosts(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = 'posts.all.' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters) {
            $query = Post::with(['category', 'comments'])
                ->withCount('comments')
                ->latest();

            // Applica filtri
            if (isset($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('content', 'like', '%' . $filters['search'] . '%');
                });
            }

            return $query->paginate($this->perPage);
        });
    }

    /**
     * Ottieni un post per ID
     */
    public function getPostById(int $id): ?Post
    {
        $cacheKey = "post.{$id}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return Post::with(['category', 'comments.user'])
                ->find($id);
        });
    }

    /**
     * Crea un nuovo post
     */
    public function createPost(array $data): Post
    {
        DB::beginTransaction();
        
        try {
            $post = Post::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? $this->generateExcerpt($data['content']),
                'category_id' => $data['category_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'featured_image' => $data['featured_image'] ?? null,
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? $this->generateExcerpt($data['content'], 160),
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            // Pulisci cache
            $this->clearCache();

            // Evento post creato
            event(new \App\Events\PostCreated($post));

            DB::commit();
            return $post;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Aggiorna un post
     */
    public function updatePost(int $id, array $data): Post
    {
        $post = Post::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            $post->update([
                'title' => $data['title'] ?? $post->title,
                'content' => $data['content'] ?? $post->content,
                'excerpt' => $data['excerpt'] ?? $this->generateExcerpt($data['content'] ?? $post->content),
                'category_id' => $data['category_id'] ?? $post->category_id,
                'status' => $data['status'] ?? $post->status,
                'featured_image' => $data['featured_image'] ?? $post->featured_image,
                'meta_title' => $data['meta_title'] ?? $post->meta_title,
                'meta_description' => $data['meta_description'] ?? $post->meta_description,
                'published_at' => $data['status'] === 'published' ? now() : $post->published_at,
            ]);

            // Pulisci cache
            $this->clearCache();

            // Evento post aggiornato
            event(new \App\Events\PostUpdated($post));

            DB::commit();
            return $post;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un post
     */
    public function deletePost(int $id): bool
    {
        $post = Post::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Elimina commenti associati
            $post->comments()->delete();
            
            // Elimina post
            $deleted = $post->delete();

            // Pulisci cache
            $this->clearCache();

            // Evento post eliminato
            event(new \App\Events\PostDeleted($post));

            DB::commit();
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ottieni post popolari
     */
    public function getPopularPosts(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "posts.popular.{$limit}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            return Post::with(['category'])
                ->where('status', 'published')
                ->withCount('comments')
                ->orderBy('comments_count', 'desc')
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Ottieni post recenti
     */
    public function getRecentPosts(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "posts.recent.{$limit}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            return Post::with(['category'])
                ->where('status', 'published')
                ->latest('published_at')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Incrementa visualizzazioni
     */
    public function incrementViews(int $id): void
    {
        Post::where('id', $id)->increment('views');
        
        // Pulisci cache per questo post
        Cache::forget("post.{$id}");
    }

    /**
     * Genera excerpt dal contenuto
     */
    protected function generateExcerpt(string $content, int $length = 150): string
    {
        $excerpt = strip_tags($content);
        $excerpt = preg_replace('/\s+/', ' ', $excerpt);
        
        if (strlen($excerpt) <= $length) {
            return $excerpt;
        }
        
        return substr($excerpt, 0, $length) . '...';
    }

    /**
     * Pulisci cache
     */
    protected function clearCache(): void
    {
        Cache::tags(['posts'])->flush();
    }

    /**
     * Ottieni statistiche post
     */
    public function getStats(): array
    {
        $cacheKey = 'posts.stats';
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return [
                'total' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'draft' => Post::where('status', 'draft')->count(),
                'total_views' => Post::sum('views'),
                'avg_views' => Post::avg('views'),
                'this_month' => Post::whereMonth('created_at', now()->month)->count(),
            ];
        });
    }
}
