<?php

namespace App\Http\Controllers;

use App\Services\DesktopBFFService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesktopBFFController extends Controller
{
    public function __construct(
        private DesktopBFFService $desktopBFFService
    ) {}

    /**
     * Ottiene ordini ottimizzati per desktop
     */
    public function getOrders(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'user_id', 'date_from', 'date_to', 'limit']);
            $orders = $this->desktopBFFService->getOrders($filters);

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
     * Ottiene un ordine specifico per desktop
     */
    public function getOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->desktopBFFService->getOrder($orderId);

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
     * Ottiene dashboard dati per desktop
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $dashboardData = $this->desktopBFFService->getDashboardData();

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
     * Ottiene prodotti ottimizzati per desktop
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category', 'min_price', 'max_price', 'search', 'limit']);
            $products = $this->desktopBFFService->getProducts($filters);

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
     * Ottiene dati per export
     */
    public function getExportData(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'orders');
            $filters = $request->only(['date_from', 'date_to', 'category', 'is_active']);
            
            $exportData = $this->desktopBFFService->getExportData($type, $filters);

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'meta' => [
                    'type' => $type,
                    'total' => count($exportData),
                    'filters' => $filters
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce la cache del desktop BFF
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->desktopBFFService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache del Desktop BFF pulita con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella pulizia della cache: ' . $e->getMessage()
            ], 500);
        }
    }
}
