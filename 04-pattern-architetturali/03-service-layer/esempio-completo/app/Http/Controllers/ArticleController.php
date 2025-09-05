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
        try {
            $filters = $request->only(['status', 'author_id', 'search']);
            $articles = $this->articleService->getArticles($filters);
            $stats = $this->articleService->getArticleStats();

            return view('articles.index', compact('articles', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra un articolo specifico
     */
    public function show(int $id): View
    {
        try {
            $article = $this->articleService->getArticleById($id);
            $relatedArticles = $this->articleService->getRelatedArticles($id);

            return view('articles.show', compact('article', 'relatedArticles'));
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
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
        try {
            $article = $this->articleService->createArticle($request->all());
            
            return redirect()
                ->route('articles.show', $article)
                ->with('success', 'Articolo creato con successo!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostra il form per modificare un articolo
     */
    public function edit(int $id): View
    {
        try {
            $article = $this->articleService->getArticleById($id);
            return view('articles.edit', compact('article'));
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $article = $this->articleService->updateArticle($id, $request->all());
            
            return redirect()
                ->route('articles.show', $article)
                ->with('success', 'Articolo aggiornato con successo!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Elimina un articolo
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->articleService->deleteArticle($id);
            
            return redirect()
                ->route('articles.index')
                ->with('success', 'Articolo eliminato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Pubblica un articolo
     */
    public function publish(int $id): RedirectResponse
    {
        try {
            $article = $this->articleService->publishArticle($id);
            
            return redirect()
                ->route('articles.show', $article)
                ->with('success', 'Articolo pubblicato con successo!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mette in bozza un articolo
     */
    public function draft(int $id): RedirectResponse
    {
        try {
            $article = $this->articleService->draftArticle($id);
            
            return redirect()
                ->route('articles.show', $article)
                ->with('success', 'Articolo messo in bozza!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cerca articoli
     */
    public function search(Request $request): View
    {
        try {
            $filters = ['search' => $request->get('q')];
            $articles = $this->articleService->getArticles($filters);
            $term = $request->get('q');

            return view('articles.search', compact('articles', 'term'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra articoli popolari
     */
    public function popular(): View
    {
        try {
            $articles = $this->articleService->getPopularArticles(20);
            return view('articles.popular', compact('articles'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mostra statistiche degli articoli
     */
    public function stats(): View
    {
        try {
            $stats = $this->articleService->getArticleStats();
            return view('articles.stats', compact('stats'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint per articoli
     */
    public function api(Request $request)
    {
        try {
            $filters = $request->only(['status', 'author_id', 'search']);
            $articles = $this->articleService->getArticles($filters);
            
            return response()->json([
                'success' => true,
                'data' => $articles,
                'count' => $articles->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
