<?php

namespace App\Http\Controllers;

use App\Services\MobileBFFService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MobileBFFController extends Controller
{
    public function __construct(
        private MobileBFFService $mobileBFFService
    ) {}

    /**
     * Ottiene ordini ottimizzati per mobile
     */
    public function getOrders(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'user_id', 'limit']);
            $orders = $this->mobileBFFService->getOrders($filters);

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
     * Ottiene un ordine specifico per mobile
     */
    public function getOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->mobileBFFService->getOrder($orderId);

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
     * Ottiene dashboard dati per mobile
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $dashboardData = $this->mobileBFFService->getDashboardData();

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
     * Ottiene prodotti ottimizzati per mobile
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category', 'search', 'limit']);
            $products = $this->mobileBFFService->getProducts($filters);

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
     * Ottiene dati offline per mobile
     */
    public function getOfflineData(): JsonResponse
    {
        try {
            $offlineData = $this->mobileBFFService->getOfflineData();

            return response()->json([
                'success' => true,
                'data' => $offlineData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati offline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce la cache del mobile BFF
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->mobileBFFService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache del Mobile BFF pulita con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella pulizia della cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
