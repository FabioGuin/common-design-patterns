<?php

namespace App\Http\Controllers;

use App\Services\WebBFFService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebBFFController extends Controller
{
    public function __construct(
        private WebBFFService $webBFFService
    ) {}

    /**
     * Ottiene ordini ottimizzati per web
     */
    public function getOrders(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'user_id', 'date_from', 'date_to', 'limit']);
            $orders = $this->webBFFService->getOrders($filters);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'meta' => [
                    'total' => count($orders),
                    'filters' => $filters
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero degli ordini: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene un ordine specifico per web
     */
    public function getOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->webBFFService->getOrder($orderId);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dell\'ordine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene dashboard dati per web
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $dashboardData = $this->webBFFService->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero della dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene prodotti ottimizzati per web
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category', 'min_price', 'max_price', 'search', 'limit']);
            $products = $this->webBFFService->getProducts($filters);

            return response()->json([
                'success' => true,
                'data' => $products,
                'meta' => [
                    'total' => count($products),
                    'filters' => $filters
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei prodotti: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce la cache del web BFF
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->webBFFService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache del Web BFF pulita con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella pulizia della cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
