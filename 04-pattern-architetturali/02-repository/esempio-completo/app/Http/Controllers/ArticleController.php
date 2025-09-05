<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService
    ) {}

    /**
     * Mostra la lista degli articoli
     */
    public function index(Request $request): View
    {
        $articles = $this->articleService->getPublishedArticles();
        $stats = $this->articleService->getArticleStats();

        return view('articles.index', compact('articles', 'stats'));
    }

    /**
     * Mostra un articolo specifico
     */
    public function show(int $id): View
    {
        $article = $this->articleService->getArticleById($id);
        
        if (!$article) {
            abort(404, 'Articolo non trovato');
        }

        $relatedArticles = $this->articleService->getRelatedArticles($id);

        return view('articles.show', compact('article', 'relatedArticles'));
    }

    /**
     * Mostra il form per creare un nuovo articolo
     */
    public function create(): View
    {
        return view('articles.create');
    }

    /**
     * Salva un nuovo articolo
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->all();
        
        // Valida i dati
        $errors = $this->articleService->validateArticleData($data);
        if (!empty($errors)) {
            return redirect()->back()
                           ->withErrors($errors)
                           ->withInput();
        }

        try {
            $article = $this->articleService->createArticle($data);
            
            return redirect()
                ->route('articles.show', $article)
                ->with('success', 'Articolo creato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante la creazione dell\'articolo: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Mostra il form per modificare un articolo
     */
    public function edit(int $id): View
    {
        $article = $this->articleService->getArticleById($id);
        
        if (!$article) {
            abort(404, 'Articolo non trovato');
        }

        return view('articles.edit', compact('article'));
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->all();
        
        // Valida i dati
        $errors = $this->articleService->validateArticleData($data);
        if (!empty($errors)) {
            return redirect()->back()
                           ->withErrors($errors)
                           ->withInput();
        }

        try {
            $success = $this->articleService->updateArticle($id, $data);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Articolo non trovato'])
                               ->withInput();
            }

            return redirect()
                ->route('articles.show', $id)
                ->with('success', 'Articolo aggiornato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante l\'aggiornamento dell\'articolo: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Elimina un articolo
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $success = $this->articleService->deleteArticle($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Articolo non trovato']);
            }

            return redirect()
                ->route('articles.index')
                ->with('success', 'Articolo eliminato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante l\'eliminazione dell\'articolo: ' . $e->getMessage()]);
        }
    }

    /**
     * Pubblica un articolo
     */
    public function publish(int $id): RedirectResponse
    {
        try {
            $success = $this->articleService->publishArticle($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Articolo non trovato']);
            }

            return redirect()
                ->route('articles.show', $id)
                ->with('success', 'Articolo pubblicato con successo!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante la pubblicazione dell\'articolo: ' . $e->getMessage()]);
        }
    }

    /**
     * Mette in bozza un articolo
     */
    public function draft(int $id): RedirectResponse
    {
        try {
            $success = $this->articleService->draftArticle($id);
            
            if (!$success) {
                return redirect()->back()
                               ->withErrors(['error' => 'Articolo non trovato']);
            }

            return redirect()
                ->route('articles.show', $id)
                ->with('success', 'Articolo messo in bozza!');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Errore durante il salvataggio in bozza: ' . $e->getMessage()]);
        }
    }

    /**
     * Cerca articoli
     */
    public function search(Request $request): View
    {
        $term = $request->get('q', '');
        $articles = $this->articleService->searchArticles($term);

        return view('articles.search', compact('articles', 'term'));
    }

    /**
     * Mostra articoli per autore
     */
    public function byAuthor(int $authorId): View
    {
        $articles = $this->articleService->getArticlesByAuthor($authorId);
        
        return view('articles.by-author', compact('articles', 'authorId'));
    }

    /**
     * Mostra articoli recenti
     */
    public function recent(): View
    {
        $articles = $this->articleService->getRecentArticles(20);
        
        return view('articles.recent', compact('articles'));
    }

    /**
     * Mostra articoli popolari
     */
    public function popular(): View
    {
        $articles = $this->articleService->getPopularArticles(20);
        
        return view('articles.popular', compact('articles'));
    }

    /**
     * Mostra statistiche degli articoli
     */
    public function stats(): View
    {
        $stats = $this->articleService->getArticleStats();
        $articlesWithStats = $this->articleService->getArticlesWithStats();
        
        return view('articles.stats', compact('stats', 'articlesWithStats'));
    }

    /**
     * API endpoint per articoli
     */
    public function api(Request $request)
    {
        $articles = $this->articleService->getPublishedArticles();
        
        return response()->json([
            'success' => true,
            'data' => $articles,
            'count' => $articles->count()
        ]);
    }
}
