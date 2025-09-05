<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * Lista post del blog
     */
    public function index(Request $request)
    {
        // Simula dati blog
        $posts = [
            [
                'id' => 1,
                'title' => 'Introduzione al Middleware Pattern',
                'excerpt' => 'Il middleware pattern è fondamentale in Laravel...',
                'author' => 'Admin',
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 2,
                'title' => 'Best Practices per Laravel',
                'excerpt' => 'Ecco le migliori pratiche per sviluppare...',
                'author' => 'Admin',
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 3,
                'title' => 'Sicurezza nelle Applicazioni Web',
                'excerpt' => 'La sicurezza è cruciale per ogni applicazione...',
                'author' => 'Admin',
                'created_at' => now()->subDays(3),
            ],
        ];

        // Log accesso alla lista
        Log::info('Blog posts accessed', [
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('blog.index', compact('posts'));
    }

    /**
     * Mostra singolo post
     */
    public function show(Request $request, int $id)
    {
        // Simula post
        $post = [
            'id' => $id,
            'title' => 'Post ' . $id . ' - Middleware Pattern',
            'content' => 'Questo è il contenuto del post ' . $id . '. Il middleware pattern è molto utile per gestire le preoccupazioni trasversali...',
            'author' => 'Admin',
            'created_at' => now()->subDays($id),
            'views' => rand(100, 1000),
        ];

        // Log visualizzazione post
        Log::info('Blog post viewed', [
            'post_id' => $id,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return view('blog.show', compact('post'));
    }

    /**
     * Crea nuovo post (richiede autenticazione e ruolo editor)
     */
    public function create()
    {
        return view('blog.create');
    }

    /**
     * Salva nuovo post
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Log creazione post
        Log::info('Blog post created', [
            'title' => $request->title,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('blog.index')
            ->with('success', 'Post creato con successo!');
    }

    /**
     * Modifica post
     */
    public function edit(int $id)
    {
        $post = [
            'id' => $id,
            'title' => 'Post ' . $id . ' - Modifica',
            'content' => 'Contenuto modificabile del post ' . $id,
        ];

        return view('blog.edit', compact('post'));
    }

    /**
     * Aggiorna post
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Log aggiornamento post
        Log::info('Blog post updated', [
            'post_id' => $id,
            'title' => $request->title,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('blog.show', $id)
            ->with('success', 'Post aggiornato con successo!');
    }

    /**
     * Elimina post
     */
    public function destroy(int $id)
    {
        // Log eliminazione post
        Log::info('Blog post deleted', [
            'post_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('blog.index')
            ->with('success', 'Post eliminato con successo!');
    }

    /**
     * API: Lista post
     */
    public function apiPosts(Request $request): JsonResponse
    {
        $posts = [
            [
                'id' => 1,
                'title' => 'API Post 1',
                'content' => 'Contenuto del post API 1',
                'author' => 'Admin',
                'created_at' => now()->subDays(1)->toISOString(),
            ],
            [
                'id' => 2,
                'title' => 'API Post 2',
                'content' => 'Contenuto del post API 2',
                'author' => 'Admin',
                'created_at' => now()->subDays(2)->toISOString(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts,
            'meta' => [
                'total' => count($posts),
                'page' => 1,
                'per_page' => 10,
            ],
        ]);
    }

    /**
     * API: Singolo post
     */
    public function apiPost(Request $request, int $id): JsonResponse
    {
        $post = [
            'id' => $id,
            'title' => 'API Post ' . $id,
            'content' => 'Contenuto del post API ' . $id,
            'author' => 'Admin',
            'created_at' => now()->subDays($id)->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => $post,
        ]);
    }

    /**
     * API: Crea post
     */
    public function apiCreatePost(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Log creazione post API
        Log::info('API post created', [
            'title' => $request->title,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        $post = [
            'id' => rand(100, 999),
            'title' => $request->title,
            'content' => $request->content,
            'author' => auth()->user()->name ?? 'Admin',
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    /**
     * API: Aggiorna post
     */
    public function apiUpdatePost(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Log aggiornamento post API
        Log::info('API post updated', [
            'post_id' => $id,
            'title' => $request->title,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        $post = [
            'id' => $id,
            'title' => $request->title,
            'content' => $request->content,
            'author' => auth()->user()->name ?? 'Admin',
            'updated_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    /**
     * API: Elimina post
     */
    public function apiDeletePost(int $id): JsonResponse
    {
        // Log eliminazione post API
        Log::info('API post deleted', [
            'post_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
