<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductServiceController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Ottiene tutti i prodotti
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();

        return response()->json([
            'success' => true,
            'data' => $products,
            'service' => 'ProductService',
            'database' => 'product_service'
        ]);
    }

    /**
     * Crea un nuovo prodotto
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'inventory' => 'integer|min:0'
        ]);

        $product = $this->productService->createProduct($request->all());

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Ottiene un prodotto specifico
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Aggiorna un prodotto
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|max:255',
            'inventory' => 'sometimes|integer|min:0'
        ]);

        $product = $this->productService->updateProduct($id, $request->all());

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Elimina un prodotto
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Aggiorna l'inventario di un prodotto
     */
    public function updateInventory(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer'
        ]);

        $product = $this->productService->updateInventory($id, $request->input('quantity'));

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Inventory updated successfully'
        ]);
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function stats(): JsonResponse
    {
        $stats = $this->productService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
