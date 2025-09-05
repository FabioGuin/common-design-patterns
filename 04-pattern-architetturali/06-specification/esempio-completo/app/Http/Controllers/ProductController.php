<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Specifications\Product\PriceRangeSpecification;
use App\Specifications\Product\InStockSpecification;
use App\Specifications\Product\CategorySpecification;
use App\Specifications\Product\NameContainsSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Mostra la lista dei prodotti
     */
    public function index(Request $request): View
    {
        $criteria = $request->only(['name', 'category_id', 'min_price', 'max_price', 'in_stock']);
        $products = $this->productService->searchProducts($criteria);
        $stats = $this->productService->getProductStatistics();

        return view('products.index', compact('products', 'stats', 'criteria'));
    }

    /**
     * Mostra un prodotto specifico
     */
    public function show(int $id): View
    {
        $product = \App\Models\Product::with('category')->findOrFail($id);
        $recommendedProducts = $this->productService->getRecommendedProducts($product);

        return view('products.show', compact('product', 'recommendedProducts'));
    }

    /**
     * API endpoint per prodotti
     */
    public function api(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only(['name', 'category_id', 'min_price', 'max_price', 'in_stock']);
            $products = $this->productService->searchProducts($criteria);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti disponibili
     */
    public function available(): JsonResponse
    {
        try {
            $products = $this->productService->getAvailableProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti disponibili',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti in un range di prezzo
     */
    public function priceRange(Request $request): JsonResponse
    {
        $request->validate([
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price'
        ]);

        try {
            $products = $this->productService->getProductsInPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti per prezzo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti per categoria
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($categoryId);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti per categoria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerca prodotti per nome
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255'
        ]);

        try {
            $products = $this->productService->searchProductsByName($request->input('q'));

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count(),
                'query' => $request->input('q')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la ricerca dei prodotti',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti popolari
     */
    public function popular(): JsonResponse
    {
        try {
            $products = $this->productService->getPopularProducts(20);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti popolari',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti recenti
     */
    public function recent(): JsonResponse
    {
        try {
            $products = $this->productService->getRecentProducts(20);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti recenti',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera prodotti in offerta
     */
    public function onSale(): JsonResponse
    {
        try {
            $products = $this->productService->getProductsOnSale();

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti in offerta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera statistiche dei prodotti
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->productService->getProductStatistics();

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
     * Recupera prodotti consigliati per un prodotto
     */
    public function recommended(int $id): JsonResponse
    {
        try {
            $product = \App\Models\Product::findOrFail($id);
            $recommendedProducts = $this->productService->getRecommendedProducts($product);

            return response()->json([
                'success' => true,
                'data' => $recommendedProducts,
                'count' => $recommendedProducts->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero dei prodotti consigliati',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce la cache dei prodotti
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->productService->clearProductCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache dei prodotti pulita con successo'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la pulizia della cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
