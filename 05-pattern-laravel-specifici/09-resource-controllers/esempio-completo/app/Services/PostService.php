<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    public function getPosts(array $filters = []): LengthAwarePaginator
    {
        $query = Post::with(['category', 'user', 'comments'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['category_id']) && $filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function createPost(array $postData): Post
    {
        // Set default values
        $postData = array_merge([
            'user_id' => auth()->id(),
            'status' => 'draft',
            'published_at' => null
        ], $postData);

        // Auto-publish if status is published
        if ($postData['status'] === 'published' && !isset($postData['published_at'])) {
            $postData['published_at'] = now();
        }

        $post = Post::create($postData);

        Log::info('Post created', [
            'post_id' => $post->id,
            'title' => $post->title,
            'user_id' => $post->user_id
        ]);

        return $post;
    }

    public function updatePost(Post $post, array $postData): Post
    {
        // Auto-publish if status is published and not already published
        if ($postData['status'] === 'published' && !$post->isPublished()) {
            $postData['published_at'] = now();
        }

        $post->update($postData);

        Log::info('Post updated', [
            'post_id' => $post->id,
            'title' => $post->title,
            'updated_fields' => array_keys($postData)
        ]);

        return $post;
    }

    public function deletePost(Post $post): bool
    {
        $postId = $post->id;
        $title = $post->title;

        $deleted = $post->delete();

        if ($deleted) {
            Log::info('Post deleted', [
                'post_id' => $postId,
                'title' => $title
            ]);
        }

        return $deleted;
    }

    public function searchPosts(string $query): LengthAwarePaginator
    {
        return Post::with(['category', 'user'])
            ->search($query)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function archivePost(Post $post): Post
    {
        $post->archive();

        Log::info('Post archived', [
            'post_id' => $post->id,
            'title' => $post->title
        ]);

        return $post;
    }

    public function restorePost(Post $post): Post
    {
        $post->restore();

        Log::info('Post restored', [
            'post_id' => $post->id,
            'title' => $post->title
        ]);

        return $post;
    }

    public function publishPost(Post $post): Post
    {
        $post->publish();

        Log::info('Post published', [
            'post_id' => $post->id,
            'title' => $post->title
        ]);

        return $post;
    }

    public function getPostsByCategory(Category $category): LengthAwarePaginator
    {
        return $category->posts()
            ->with(['user', 'comments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getPublishedPosts(): LengthAwarePaginator
    {
        return Post::published()
            ->with(['category', 'user', 'comments'])
            ->orderBy('published_at', 'desc')
            ->paginate(10);
    }

    public function getDraftPosts(): LengthAwarePaginator
    {
        return Post::draft()
            ->with(['category', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getArchivedPosts(): LengthAwarePaginator
    {
        return Post::archived()
            ->with(['category', 'user'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    }

    public function getPostStats(): array
    {
        return [
            'total' => Post::count(),
            'published' => Post::published()->count(),
            'draft' => Post::draft()->count(),
            'archived' => Post::archived()->count(),
            'this_month' => Post::whereMonth('created_at', now()->month)->count(),
            'this_year' => Post::whereYear('created_at', now()->year)->count()
        ];
    }

    public function getPopularPosts(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Post::published()
            ->with(['category', 'user'])
            ->withCount('comments')
            ->orderBy('comments_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentPosts(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Post::published()
            ->with(['category', 'user'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
