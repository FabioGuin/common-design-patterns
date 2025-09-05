<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderServiceController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Ottiene tutti gli ordini
     */
    public function index(): JsonResponse
    {
        $orders = $this->orderService->getAllOrders();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'service' => 'OrderService',
            'database' => 'order_service'
        ]);
    }

    /**
     * Crea un nuovo ordine
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0'
        ]);

        $order = $this->orderService->createOrder($request->all());

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order created successfully'
        ], 201);
    }

    /**
     * Ottiene un ordine specifico
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Aggiorna lo stato di un ordine
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,paid,shipped,delivered,cancelled'
        ]);

        $order = $this->orderService->updateOrderStatus($id, $request->input('status'));

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Elimina un ordine
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->orderService->deleteOrder($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function stats(): JsonResponse
    {
        $stats = $this->orderService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
