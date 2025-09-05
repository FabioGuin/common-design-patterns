<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use App\DTOs\Article\CreateArticleDTO;
use App\DTOs\Article\UpdateArticleDTO;
use App\DTOs\Article\ArticleResponseDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        $filters = $request->only(['status', 'author_id', 'search', 'sort', 'direction']);
        $articles = $this->articleService->getArticles($filters);
        $stats = $this->articleService->getArticleStats();

        return view('articles.index', compact('articles', 'stats'));
    }

    /**
     * Mostra un articolo specifico
     */
    public function show(int $id): View
    {
        $article = $this->articleService->getArticle($id);
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
    public function store(Request $request): JsonResponse
    {
        try {
            $dto = new CreateArticleDTO(
                title: $request->input('title'),
                content: $request->input('content'),
                userId: $request->input('user_id'),
                excerpt: $request->input('excerpt'),
                status: $request->input('status', 'draft')
            );

            $responseDto = $this->articleService->createArticle($dto);

            return response()->json([
                'success' => true,
                'message' => 'Articolo creato con successo!',
                'data' => $responseDto->toArray()
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'articolo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostra il form per modificare un articolo
     */
    public function edit(int $id): View
    {
        $article = $this->articleService->getArticle($id);
        return view('articles.edit', compact('article'));
    }

    /**
     * Aggiorna un articolo esistente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $dto = new UpdateArticleDTO(
                title: $request->input('title'),
                content: $request->input('content'),
                excerpt: $request->input('excerpt'),
                status: $request->input('status')
            );

            $responseDto = $this->articleService->updateArticle($id, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Articolo aggiornato con successo!',
                'data' => $responseDto->toArray()
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'articolo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un articolo
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->articleService->deleteArticle($id);

            return response()->json([
                'success' => true,
                'message' => 'Articolo eliminato con successo!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione dell\'articolo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pubblica un articolo
     */
    public function publish(int $id): JsonResponse
    {
        try {
            $responseDto = $this->articleService->publishArticle($id);

            return response()->json([
                'success' => true,
                'message' => 'Articolo pubblicato con successo!',
                'data' => $responseDto->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la pubblicazione dell\'articolo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mette in bozza un articolo
     */
    public function draft(int $id): JsonResponse
    {
        try {
            $responseDto = $this->articleService->draftArticle($id);

            return response()->json([
                'success' => true,
                'message' => 'Articolo messo in bozza!',
                'data' => $responseDto->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il salvataggio in bozza',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerca articoli
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->input('q');
            $articles = $this->articleService->searchArticles($term);

            return response()->json([
                'success' => true,
                'data' => $articles->map(function ($article) {
                    return ArticleResponseDTO::fromModel($article)->toArray();
                }),
                'count' => $articles->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera articoli popolari
     */
    public function popular(): JsonResponse
    {
        try {
            $articles = $this->articleService->getPopularArticles(20);

            return response()->json([
                'success' => true,
                'data' => $articles->map(function ($article) {
                    return ArticleResponseDTO::fromModel($article)->toArray();
                }),
                'count' => $articles->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero degli articoli popolari',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera statistiche degli articoli
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->articleService->getArticleStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero delle statistiche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint per articoli
     */
    public function api(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'author_id', 'search', 'sort', 'direction']);
            $articles = $this->articleService->getArticles($filters);

            return response()->json([
                'success' => true,
                'data' => $articles->map(function ($article) {
                    return ArticleResponseDTO::fromModel($article)->toArray();
                }),
                'count' => $articles->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero degli articoli',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
