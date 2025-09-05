<?php

namespace App\Http\Controllers;

use App\Services\ECommerceFacade;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    private ECommerceFacade $ecommerceFacade;
    private InventoryService $inventoryService;

    public function __construct(ECommerceFacade $ecommerceFacade, InventoryService $inventoryService)
    {
        $this->ecommerceFacade = $ecommerceFacade;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Mostra la pagina principale degli ordini
     */
    public function index()
    {
        $products = $this->inventoryService->getAllProducts();
        $systemStats = $this->ecommerceFacade->getSystemStats();

        return view('orders.index', [
            'products' => $products,
            'systemStats' => $systemStats['success'] ? $systemStats['stats'] : [],
        ]);
    }

    /**
     * Processa un nuovo ordine
     */
    public function processOrder(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'customer_email' => 'required|email',
            'shipping_address' => 'required|string|max:500',
            'payment.card_number' => 'required|string',
            'payment.cvv' => 'required|string',
        ]);

        try {
            $orderData = [
                'order_id' => 'ORD_' . uniqid(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'customer_email' => $request->customer_email,
                'shipping_address' => $request->shipping_address,
                'payment' => [
                    'card_number' => $request->input('payment.card_number'),
                    'cvv' => $request->input('payment.cvv'),
                    'amount' => $this->calculateOrderAmount($request->product_id, $request->quantity),
                ],
            ];

            $result = $this->ecommerceFacade->processOrder($orderData);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
            'product_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'customer_email' => 'required|email',
            'payment_id' => 'required|string',
            'shipment_id' => 'required|string',
        ]);

        try {
            $orderData = [
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'customer_email' => $request->customer_email,
                'payment_id' => $request->payment_id,
                'shipment_id' => $request->shipment_id,
            ];

            $result = $this->ecommerceFacade->cancelOrder($request->order_id, $orderData);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le informazioni di un ordine
     */
    public function getOrderInfo(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        try {
            $result = $this->ecommerceFacade->getOrderInfo($request->order_id);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving order information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera un report completo
     */
    public function generateReport(): JsonResponse
    {
        try {
            $result = $this->ecommerceFacade->generateCompleteReport();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del sistema
     */
    public function getSystemStats(): JsonResponse
    {
        try {
            $result = $this->ecommerceFacade->getSystemStats();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving system stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcola l'importo dell'ordine
     */
    private function calculateOrderAmount(string $productId, int $quantity): float
    {
        $product = $this->inventoryService->getProduct($productId);
        if (!$product) {
            return 0.0;
        }

        return $product['price'] * $quantity;
    }
}
