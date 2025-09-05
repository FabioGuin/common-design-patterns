<?php

namespace App\Http\Controllers;

use App\Services\Blog\PostService;
use App\Services\Blog\CommentService;
use App\Services\Blog\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    public function __construct(
        private PostService $postService,
        private CommentService $commentService,
        private CategoryService $categoryService
    ) {}

    /**
     * Lista post del blog
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status']);
        $posts = $this->postService->getAllPosts($filters);
        $categories = $this->categoryService->getAllCategories();
        $stats = $this->postService->getStats();

        return view('blog.index', compact('posts', 'categories', 'stats'));
    }

    /**
     * Mostra singolo post
     */
    public function show(int $id)
    {
        $post = $this->postService->getPostById($id);
        
        if (!$post) {
            abort(404, 'Post non trovato');
        }

        // Incrementa visualizzazioni
        $this->postService->incrementViews($id);

        // Post correlati
        $relatedPosts = $this->postService->getRelatedPosts($id, 3);

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Crea nuovo post
     */
    public function create()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('blog.create', compact('categories'));
    }

    /**
     * Salva nuovo post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            $post = $this->postService->createPost($request->all());
            
            return redirect()
                ->route('blog.show', $post)
                ->with('success', 'Post creato con successo!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Errore nella creazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Modifica post
     */
    public function edit(int $id)
    {
        $post = $this->postService->getPostById($id);
        $categories = $this->categoryService->getAllCategories();
        
        return view('blog.edit', compact('post', 'categories'));
    }

    /**
     * Aggiorna post
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            $post = $this->postService->updatePost($id, $request->all());
            
            return redirect()
                ->route('blog.show', $post)
                ->with('success', 'Post aggiornato con successo!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Errore nell\'aggiornamento del post: ' . $e->getMessage());
        }
    }

    /**
     * Elimina post
     */
    public function destroy(int $id)
    {
        try {
            $this->postService->deletePost($id);
            
            return redirect()
                ->route('blog.index')
                ->with('success', 'Post eliminato con successo!');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Errore nell\'eliminazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Area amministrativa
     */
    public function admin(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status']);
        $posts = $this->postService->getAllPosts($filters);
        $categories = $this->categoryService->getAllCategories();
        $stats = $this->postService->getStats();

        return view('blog.admin', compact('posts', 'categories', 'stats'));
    }

    /**
     * Gestione commenti
     */
    public function comments(Request $request)
    {
        $filters = $request->only(['status', 'post_id']);
        $comments = $this->commentService->getAllComments($filters);
        $posts = $this->postService->getAllPosts();

        return view('blog.comments', compact('comments', 'posts'));
    }

    /**
     * API: Lista post
     */
    public function apiPosts(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id', 'status']);
        $posts = $this->postService->getAllPosts($filters);

        return response()->api($posts->items(), 'Posts retrieved successfully');
    }

    /**
     * API: Singolo post
     */
    public function apiPost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);
        
        if (!$post) {
            return response()->apiError('Post not found', 404);
        }

        return response()->api($post, 'Post retrieved successfully');
    }

    /**
     * API: Crea post
     */
    public function apiCreatePost(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
        ]);

        try {
            $post = $this->postService->createPost($request->all());
            return response()->api($post, 'Post created successfully', 201);
        } catch (\Exception $e) {
            return response()->apiError('Error creating post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Aggiorna post
     */
    public function apiUpdatePost(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published',
        ]);

        try {
            $post = $this->postService->updatePost($id, $request->all());
            return response()->api($post, 'Post updated successfully');
        } catch (\Exception $e) {
            return response()->apiError('Error updating post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Elimina post
     */
    public function apiDeletePost(int $id): JsonResponse
    {
        try {
            $this->postService->deletePost($id);
            return response()->api(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            return response()->apiError('Error deleting post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Statistiche
     */
    public function apiStats(): JsonResponse
    {
        $stats = $this->postService->getStats();
        return response()->api($stats, 'Stats retrieved successfully');
    }
}
