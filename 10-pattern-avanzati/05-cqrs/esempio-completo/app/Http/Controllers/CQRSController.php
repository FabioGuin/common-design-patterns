<?php

namespace App\Http\Controllers;

use App\Commands\CreateProductCommand;
use App\Commands\UpdateProductCommand;
use App\Commands\CreateOrderCommand;
use App\Handlers\CreateProductHandler;
use App\Handlers\UpdateProductHandler;
use App\Handlers\CreateOrderHandler;
use App\Services\ProductQueryService;
use App\Services\OrderQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CQRSController extends Controller
{
    public function __construct(
        private CreateProductHandler $createProductHandler,
        private UpdateProductHandler $updateProductHandler,
        private CreateOrderHandler $createOrderHandler,
        private ProductQueryService $productQueryService,
        private OrderQueryService $orderQueryService
    ) {}

    public function index()
    {
        return view('cqrs.index');
    }

    // COMMAND SIDE - Scrittura
    public function createProduct(Request $request): JsonResponse
    {
        try {
            $command = new CreateProductCommand(
                name: $request->input('name'),
                description: $request->input('description'),
                price: (float) $request->input('price'),
                stock: (int) $request->input('stock'),
                category: $request->input('category'),
                attributes: $request->input('attributes', [])
            );

            $product = $this->createProductHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Prodotto creato con successo',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        try {
            $command = new UpdateProductCommand(
                id: $id,
                name: $request->input('name'),
                description: $request->input('description'),
                price: $request->input('price') ? (float) $request->input('price') : null,
                stock: $request->input('stock') ? (int) $request->input('stock') : null,
                category: $request->input('category'),
                attributes: $request->input('attributes')
            );

            $product = $this->updateProductHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Prodotto aggiornato con successo',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createOrder(Request $request): JsonResponse
    {
        try {
            $command = new CreateOrderCommand(
                userId: (int) $request->input('user_id'),
                items: $request->input('items'),
                shippingAddress: $request->input('shipping_address'),
                billingAddress: $request->input('billing_address')
            );

            $order = $this->createOrderHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // QUERY SIDE - Lettura
    public function searchProducts(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category', 'min_price', 'max_price', 
            'available', 'sort_by', 'sort_direction', 'limit', 'offset'
        ]);

        $products = $this->productQueryService->searchProducts($filters);

        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count()
        ]);
    }

    public function getProduct(int $id): JsonResponse
    {
        $product = $this->productQueryService->getProductById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Prodotto non trovato'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    public function getProductStats(): JsonResponse
    {
        $stats = $this->productQueryService->getProductStats();

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function getOrdersByUser(Request $request, int $userId): JsonResponse
    {
        $filters = $request->only([
            'status', 'start_date', 'end_date', 'sort_by', 'sort_direction', 'limit', 'offset'
        ]);

        $orders = $this->orderQueryService->getOrdersByUser($userId, $filters);

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'count' => $orders->count()
        ]);
    }

    public function getOrderStats(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $stats = $this->orderQueryService->getOrderStats($userId);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
