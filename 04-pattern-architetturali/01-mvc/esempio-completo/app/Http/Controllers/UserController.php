<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Mostra la lista degli utenti
     */
    public function index(Request $request): View
    {
        $query = User::withCount(['articles as published_articles_count' => function ($q) {
            $q->published();
        }]);

        // Filtro per ruolo
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filtro per stato attivo
        if ($request->has('status') && $request->status !== 'all') {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Filtro per ricerca
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Ordinamento
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['name', 'email', 'role', 'created_at', 'published_articles_count'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Mostra un utente specifico
     */
    public function show(User $user): View
    {
        $user->loadCount(['articles as published_articles_count' => function ($q) {
            $q->published();
        }]);

        $recentArticles = $user->getRecentArticles(5);
        $stats = $user->getStats();

        return view('users.show', compact('user', 'recentArticles', 'stats'));
    }

    /**
     * Mostra il profilo pubblico di un utente
     */
    public function profile(User $user): View
    {
        $articles = $user->articles()
                        ->published()
                        ->recent()
                        ->paginate(10);

        return view('users.profile', compact('user', 'articles'));
    }

    /**
     * Mostra articoli di un utente specifico
     */
    public function articles(User $user, Request $request): View
    {
        $query = $user->articles();

        // Filtro per stato
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        // Filtro per ricerca
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Ordinamento
        $sortBy = $request->get('sort', 'published_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['title', 'published_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $articles = $query->paginate(10)->withQueryString();

        return view('users.articles', compact('user', 'articles'));
    }

    /**
     * Attiva un utente
     */
    public function activate(User $user)
    {
        $user->activate();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Utente attivato con successo!');
    }

    /**
     * Disattiva un utente
     */
    public function deactivate(User $user)
    {
        $user->deactivate();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Utente disattivato con successo!');
    }

    /**
     * Cambia il ruolo di un utente
     */
    public function changeRole(User $user, Request $request)
    {
        $request->validate([
            'role' => 'required|in:user,editor,admin'
        ]);

        $user->update(['role' => $request->role]);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Ruolo utente aggiornato con successo!');
    }

    /**
     * API endpoint per utenti (se necessario)
     */
    public function api(Request $request)
    {
        $users = User::withCount('articles')
                    ->active()
                    ->paginate(15);

        return response()->json($users);
    }

    /**
     * Mostra statistiche degli utenti
     */
    public function stats(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'users_with_articles' => User::withArticles()->count(),
            'total_articles' => \App\Models\Article::count(),
            'published_articles' => \App\Models\Article::published()->count(),
            'draft_articles' => \App\Models\Article::draft()->count(),
        ];

        $topAuthors = User::withCount(['articles as published_articles_count' => function ($q) {
            $q->published();
        }])
        ->orderBy('published_articles_count', 'desc')
        ->limit(10)
        ->get();

        return view('users.stats', compact('stats', 'topAuthors'));
    }
}
