<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $posts = $this->postService->getPosts($request->all());
            
            Log::info('Posts list retrieved', [
                'count' => $posts->count(),
                'filters' => $request->all()
            ]);

            return view('resource-controllers.posts.index', compact('posts'));
        } catch (\Exception $e) {
            Log::error('Error retrieving posts list', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Errore nel recupero dei post.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('resource-controllers.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        try {
            $postData = $request->validated();
            $post = $this->postService->createPost($postData);

            Log::info('Post created successfully', [
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->route('posts.show', $post)
                ->with('success', 'Post creato con successo!');
        } catch (\Exception $e) {
            Log::error('Error creating post', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nella creazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        try {
            $post->load(['category', 'comments.user']);
            
            Log::info('Post retrieved', [
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return view('resource-controllers.posts.show', compact('post'));
        } catch (\Exception $e) {
            Log::error('Error retrieving post', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('posts.index')
                ->with('error', 'Errore nel recupero del post.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = \App\Models\Category::all();
        return view('resource-controllers.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post)
    {
        try {
            $postData = $request->validated();
            $this->postService->updatePost($post, $postData);

            Log::info('Post updated successfully', [
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->route('posts.show', $post)
                ->with('success', 'Post aggiornato con successo!');
        } catch (\Exception $e) {
            Log::error('Error updating post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nell\'aggiornamento del post: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $this->postService->deletePost($post);

            Log::info('Post deleted successfully', [
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->route('posts.index')
                ->with('success', 'Post eliminato con successo!');
        } catch (\Exception $e) {
            Log::error('Error deleting post', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'eliminazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $posts = $this->postService->searchPosts($query);

            Log::info('Posts searched', [
                'query' => $query,
                'count' => $posts->count()
            ]);

            return view('resource-controllers.posts.index', compact('posts', 'query'));
        } catch (\Exception $e) {
            Log::error('Error searching posts', [
                'query' => $request->get('q'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('posts.index')
                ->with('error', 'Errore nella ricerca dei post.');
        }
    }

    /**
     * Archive post
     */
    public function archive(Post $post)
    {
        try {
            $this->postService->archivePost($post);

            Log::info('Post archived', [
                'post_id' => $post->id
            ]);

            return redirect()->route('posts.index')
                ->with('success', 'Post archiviato con successo!');
        } catch (\Exception $e) {
            Log::error('Error archiving post', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'archiviazione del post: ' . $e->getMessage());
        }
    }

    /**
     * Restore archived post
     */
    public function restore(Post $post)
    {
        try {
            $this->postService->restorePost($post);

            Log::info('Post restored', [
                'post_id' => $post->id
            ]);

            return redirect()->route('posts.index')
                ->with('success', 'Post ripristinato con successo!');
        } catch (\Exception $e) {
            Log::error('Error restoring post', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nel ripristino del post: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for posts
     */
    public function apiIndex(Request $request)
    {
        try {
            $posts = $this->postService->getPosts($request->all());
            
            return PostResource::collection($posts);
        } catch (\Exception $e) {
            Log::error('Error in API posts index', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero dei post'
            ], 500);
        }
    }

    /**
     * API endpoint for single post
     */
    public function apiShow(Post $post)
    {
        try {
            $post->load(['category', 'comments.user']);
            
            return new PostResource($post);
        } catch (\Exception $e) {
            Log::error('Error in API post show', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero del post'
            ], 500);
        }
    }
}
