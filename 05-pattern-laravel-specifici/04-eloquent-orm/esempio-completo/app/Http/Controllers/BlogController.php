<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category', 'tags'])
                    ->withCount('comments');

        // Apply filters
        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $query->inCategory($request->category);
        }

        if ($request->has('tag')) {
            $query->withTags([$request->tag]);
        }

        if ($request->has('author')) {
            $query->byUser($request->author);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply sorting
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            default:
                $query->recent();
        }

        // Get posts with pagination
        $posts = $query->paginate(10)->withQueryString();

        // Get filter options
        $categories = Category::withCount('posts')->get();
        $tags = Tag::withCount('posts')->get();
        $authors = User::withCount('posts')->get();

        // Get popular posts for sidebar
        $popularPosts = Post::published()
                           ->popular()
                           ->limit(5)
                           ->get();

        // Get recent posts for sidebar
        $recentPosts = Post::published()
                          ->recent()
                          ->limit(5)
                          ->get();

        return view('blog.index', compact(
            'posts',
            'categories',
            'tags',
            'authors',
            'popularPosts',
            'recentPosts'
        ));
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        // Increment views count
        $post->incrementViews();

        // Load relationships
        $post->load(['user', 'category', 'tags', 'comments.user']);

        // Get related posts
        $relatedPosts = $post->getRelatedPosts(4);

        // Get popular posts for sidebar
        $popularPosts = Post::published()
                           ->where('id', '!=', $post->id)
                           ->popular()
                           ->limit(5)
                           ->get();

        return view('blog.show', compact('post', 'relatedPosts', 'popularPosts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('blog.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $post = Post::create([
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'user_id' => auth()->id(),
                'published_at' => $request->status === 'published' ? now() : null,
            ]);

            // Attach tags
            if ($request->has('tags')) {
                $post->tags()->attach($request->tags);
            }

            // Handle featured image
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('posts', 'public');
                $post->update(['featured_image' => $imagePath]);
            }

            DB::commit();

            Log::info('Post created', [
                'post_id' => $post->id,
                'title' => $post->title,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('blog.show', $post)
                ->with('success', 'Post creato con successo!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Errore nella creazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the post.
     */
    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('blog.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $post->update([
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'published_at' => $request->status === 'published' ? now() : null,
            ]);

            // Sync tags
            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            } else {
                $post->tags()->detach();
            }

            // Handle featured image
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('posts', 'public');
                $post->update(['featured_image' => $imagePath]);
            }

            DB::commit();

            Log::info('Post updated', [
                'post_id' => $post->id,
                'title' => $post->title,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('blog.show', $post)
                ->with('success', 'Post aggiornato con successo!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Post update failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Errore nell\'aggiornamento del post: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();

            Log::info('Post deleted', [
                'post_id' => $post->id,
                'title' => $post->title,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('blog.index')
                ->with('success', 'Post eliminato con successo!');

        } catch (\Exception $e) {
            Log::error('Post deletion failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->with('error', 'Errore nell\'eliminazione del post: ' . $e->getMessage());
        }
    }

    /**
     * API: Get posts
     */
    public function apiPosts(Request $request): JsonResponse
    {
        $query = Post::with(['user', 'category', 'tags'])
                    ->withCount('comments');

        // Apply filters
        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('category')) {
            $query->inCategory($request->category);
        }

        if ($request->has('tag')) {
            $query->withTags([$request->tag]);
        }

        if ($request->has('author')) {
            $query->byUser($request->author);
        }

        // Apply sorting
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            default:
                $query->recent();
        }

        $posts = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * API: Get single post
     */
    public function apiPost(Post $post): JsonResponse
    {
        $post->load(['user', 'category', 'tags', 'comments.user']);

        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => $post,
        ]);
    }

    /**
     * API: Create post
     */
    public function apiCreatePost(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published',
        ]);

        try {
            DB::beginTransaction();

            $post = Post::create([
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'user_id' => auth()->id(),
                'published_at' => $request->status === 'published' ? now() : null,
            ]);

            if ($request->has('tags')) {
                $post->tags()->attach($request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post->load(['user', 'category', 'tags']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update post
     */
    public function apiUpdatePost(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:draft,published',
        ]);

        try {
            DB::beginTransaction();

            $post->update([
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'category_id' => $request->category_id,
                'status' => $request->status,
                'published_at' => $request->status === 'published' ? now() : null,
            ]);

            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            } else {
                $post->tags()->detach();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post->load(['user', 'category', 'tags']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Delete post
     */
    public function apiDeletePost(Post $post): JsonResponse
    {
        try {
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting post: ' . $e->getMessage(),
            ], 500);
        }
    }
}
