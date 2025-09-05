<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Http\Requests\StoreArticleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Mostra la lista degli articoli
     */
    public function index(Request $request): View
    {
        $query = Article::with('user');

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

        // Filtro per autore
        if ($request->has('author') && $request->author) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->author}%");
            });
        }

        // Ordinamento
        $sortBy = $request->get('sort', 'published_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['title', 'published_at', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $articles = $query->paginate(10)->withQueryString();

        return view('articles.index', compact('articles'));
    }

    /**
     * Mostra il form per creare un nuovo articolo
     */
    public function create(): View
    {
        $users = User::active()->get();
        return view('articles.create', compact('users'));
    }

    /**
     * Salva un nuovo articolo
     */
    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = Article::create($request->validated());

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Articolo creato con successo!');
    }

    /**
     * Mostra un articolo specifico
     */
    public function show(Article $article): View
    {
        $article->load('user');
        
        // Incrementa il contatore di visualizzazioni (se implementato)
        // $article->increment('views_count');

        return view('articles.show', compact('article'));
    }

    /**
     * Mostra il form per modificare un articolo
     */
    public function edit(Article $article): View
    {
        $users = User::active()->get();
        return view('articles.edit', compact('article', 'users'));
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function update(StoreArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($request->validated());

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Articolo aggiornato con successo!');
    }

    /**
     * Elimina un articolo
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('success', 'Articolo eliminato con successo!');
    }

    /**
     * Pubblica un articolo
     */
    public function publish(Article $article): RedirectResponse
    {
        $article->publish();

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Articolo pubblicato con successo!');
    }

    /**
     * Mette in bozza un articolo
     */
    public function draft(Article $article): RedirectResponse
    {
        $article->draft();

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Articolo messo in bozza!');
    }

    /**
     * Mostra articoli per autore
     */
    public function byAuthor(User $user): View
    {
        $articles = $user->articles()
                        ->published()
                        ->recent()
                        ->paginate(10);

        return view('articles.by-author', compact('articles', 'user'));
    }

    /**
     * Mostra articoli per categoria (se implementata)
     */
    public function byCategory(Request $request): View
    {
        // Implementazione per categorie se necessaria
        $articles = Article::published()
                          ->recent()
                          ->paginate(10);

        return view('articles.by-category', compact('articles'));
    }

    /**
     * API endpoint per articoli (se necessario)
     */
    public function api(Request $request)
    {
        $articles = Article::with('user')
                          ->published()
                          ->recent()
                          ->paginate(10);

        return response()->json($articles);
    }
}
