<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Controlla autorizzazione per vedere la lista
            $this->authorize('viewAny', Post::class);

            $posts = Post::with(['user', 'category', 'comments'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            Log::info('Posts list accessed', [
                'user_id' => auth()->id(),
                'count' => $posts->count()
            ]);

            return view('policy.posts.index', compact('posts'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Posts list access denied', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per visualizzare i post.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $this->authorize('create', Post::class);

            $categories = \App\Models\Category::all();
            return view('policy.posts.create', compact('categories'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post creation access denied', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per creare post.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Post::class);

            $postData = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:draft,published,archived'
            ]);

            $postData['user_id'] = auth()->id();
            $post = Post::create($postData);

            Log::info('Post created', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->route('posts.show', $post)
                ->with('success', 'Post creato con successo!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post creation denied', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per creare post.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        try {
            $this->authorize('view', $post);

            $post->load(['user', 'category', 'comments.user']);

            Log::info('Post viewed', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return view('policy.posts.show', compact('post'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post view denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per visualizzare questo post.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        try {
            $this->authorize('update', $post);

            $categories = \App\Models\Category::all();
            return view('policy.posts.edit', compact('post', 'categories'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post edit access denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per modificare questo post.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        try {
            $this->authorize('update', $post);

            $postData = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'status' => 'required|in:draft,published,archived'
            ]);

            $post->update($postData);

            Log::info('Post updated', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->route('posts.show', $post)
                ->with('success', 'Post aggiornato con successo!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post update denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per modificare questo post.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $this->authorize('delete', $post);

            $postId = $post->id;
            $title = $post->title;
            $post->delete();

            Log::info('Post deleted', [
                'user_id' => auth()->id(),
                'post_id' => $postId,
                'title' => $title
            ]);

            return redirect()->route('posts.index')
                ->with('success', 'Post eliminato con successo!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post deletion denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per eliminare questo post.');
        }
    }

    /**
     * Publish the post
     */
    public function publish(Post $post)
    {
        try {
            $this->authorize('publish', $post);

            $post->update([
                'status' => 'published',
                'published_at' => now()
            ]);

            Log::info('Post published', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->back()->with('success', 'Post pubblicato con successo!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post publish denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per pubblicare questo post.');
        }
    }

    /**
     * Archive the post
     */
    public function archive(Post $post)
    {
        try {
            $this->authorize('archive', $post);

            $post->update(['status' => 'archived']);

            Log::info('Post archived', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'title' => $post->title
            ]);

            return redirect()->back()->with('success', 'Post archiviato con successo!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post archive denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per archiviare questo post.');
        }
    }

    /**
     * Show post comments
     */
    public function comments(Post $post)
    {
        try {
            $this->authorize('viewComments', $post);

            $comments = $post->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            Log::info('Post comments viewed', [
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ]);

            return view('policy.posts.comments', compact('post', 'comments'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post comments view denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per visualizzare i commenti di questo post.');
        }
    }

    /**
     * Moderate the post
     */
    public function moderate(Post $post)
    {
        try {
            $this->authorize('moderate', $post);

            Log::info('Post moderation accessed', [
                'user_id' => auth()->id(),
                'post_id' => $post->id
            ]);

            return view('policy.posts.moderate', compact('post'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Post moderation denied', [
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Non hai i permessi per moderare questo post.');
        }
    }
}
