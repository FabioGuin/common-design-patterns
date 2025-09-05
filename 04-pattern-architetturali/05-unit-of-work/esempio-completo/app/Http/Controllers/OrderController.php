<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Mostra la lista degli ordini
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $orders = $this->orderService->getOrders($filters);
        $stats = $this->orderService->getOrderStatistics();

        return view('orders.index', compact('orders', 'stats'));
    }

    /**
     * Mostra un ordine specifico
     */
    public function show(int $id): View
    {
        $order = $this->orderService->getOrder($id);
        return view('orders.show', compact('order'));
    }

    /**
     * Mostra il form per creare un nuovo ordine
     */
    public function create(): View
    {
        return view('orders.create');
    }

    /**
     * Salva un nuovo ordine
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'billing_address' => 'required|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $request->only([
                    'customer_name', 'customer_email', 'customer_phone',
                    'shipping_address', 'billing_address'
                ]),
                $request->input('products')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo!',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dell\'ordine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostra il form per modificare un ordine
     */
    public function edit(int $id): View
    {
        $order = $this->orderService->getOrder($id);
        return view('orders.edit', compact('order'));
    }

    /**
     * Aggiorna un ordine esistente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'customer_phone' => 'sometimes|string|max:20',
            'shipping_address' => 'sometimes|string|max:500',
            'billing_address' => 'sometimes|string|max:500',
            'products' => 'sometimes|array|min:1',
            'products.*.id' => 'required_with:products|integer|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
        ]);

        try {
            $order = $this->orderService->updateOrder(
                $id,
                $request->only([
                    'customer_name', 'customer_email', 'customer_phone',
                    'shipping_address', 'billing_address'
                ]),
                $request->input('products', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine aggiornato con successo!',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento dell\'ordine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancella un ordine
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if (!$this->orderService->canCancelOrder($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'ordine non può essere cancellato'
                ], 400);
            }

            $this->orderService->cancelOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Ordine cancellato con successo!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la cancellazione dell\'ordine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completa un ordine
     */
    public function complete(int $id): JsonResponse
    {
        try {
            if (!$this->orderService->canCompleteOrder($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'ordine non può essere completato'
                ], 400);
            }

            $order = $this->orderService->completeOrder($id);

            return response()->json([
                'success' => true,
                'message' => 'Ordine completato con successo!',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il completamento dell\'ordine',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa un pagamento per un ordine
     */
    public function processPayment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'method' => 'required|string|in:credit_card,paypal,bank_transfer',
            'status' => 'required|string|in:success,failed,pending',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        try {
            $order = $this->orderService->processPayment($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pagamento processato con successo!',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il processamento del pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche degli ordini
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->orderService->getOrderStatistics();

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
     * API endpoint per ordini
     */
    public function api(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
            $orders = $this->orderService->getOrders($filters);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => $orders->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero degli ordini',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
