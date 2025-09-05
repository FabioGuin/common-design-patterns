<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\OrderService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('hexagonal-architecture.example');
    }

    /**
     * Test del pattern Hexagonal Architecture
     */
    public function test()
    {
        try {
            // Test del pattern
            $results = $this->testHexagonalArchitecture();
            
            return response()->json([
                'success' => true,
                'message' => 'Test Hexagonal Architecture completato',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuovo ordine
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'sometimes|numeric|min:0'
        ]);

        try {
            $order = $this->orderService->createOrder($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo',
                'data' => $order->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Aggiorna un ordine
     */
    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email|max:255',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'discount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|in:pending,paid,shipped,delivered,cancelled'
        ]);

        try {
            $order = $this->orderService->updateOrder($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ordine aggiornato con successo',
                'data' => $order->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        try {
            $order = $this->orderService->cancelOrder($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'message' => 'Ordine cancellato con successo',
                'data' => $order->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella cancellazione: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottiene un ordine
     */
    public function getOrder($id)
    {
        try {
            $order = $this->orderService->getOrder($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordine non trovato'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista tutti gli ordini
     */
    public function listOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 100);
            $offset = $request->get('offset', 0);
            
            $orders = $this->orderService->getAllOrders($limit, $offset);
            $ordersArray = array_map(function($order) {
                return $order->toArray();
            }, $orders);

            return response()->json([
                'success' => true,
                'data' => $ordersArray,
                'count' => count($ordersArray)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa un pagamento
     */
    public function processPayment($id)
    {
        try {
            $result = $this->orderService->processPayment($id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Pagamento processato con successo' : 'Errore nel pagamento',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel processing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invia una notifica
     */
    public function sendNotification(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:confirmation,cancellation,shipping,delivery'
        ]);

        try {
            $result = $this->orderService->sendNotification($id, $request->input('type'));

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Notifica inviata con successo' : 'Errore nell\'invio',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'invio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche
     */
    public function getStats()
    {
        try {
            $orders = $this->orderService->getAllOrders();
            
            $stats = [
                'total_orders' => count($orders),
                'orders_by_status' => [],
                'total_revenue' => 0,
                'average_order_value' => 0
            ];
            
            $totalRevenue = 0;
            $statusCounts = [];
            
            foreach ($orders as $order) {
                $status = $order->getStatus();
                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                
                if ($status !== 'cancelled') {
                    $totalRevenue += $order->getTotalAmount();
                }
            }
            
            $stats['orders_by_status'] = $statusCounts;
            $stats['total_revenue'] = $totalRevenue;
            $stats['average_order_value'] = count($orders) > 0 ? $totalRevenue / count($orders) : 0;

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test del pattern Hexagonal Architecture
     */
    private function testHexagonalArchitecture()
    {
        $results = [];
        
        try {
            // Test 1: Creazione ordine
            $orderData = [
                'customer_name' => 'Test Customer',
                'customer_email' => 'test@example.com',
                'items' => [
                    ['product_id' => 'prod_1', 'quantity' => 2, 'price' => 50.00]
                ],
                'discount' => 10.00
            ];
            
            $order = $this->orderService->createOrder($orderData);
            $results['create_order'] = $order ? 'success' : 'failed';
            
            if ($order) {
                $orderId = $order->getId();
                
                // Test 2: Aggiornamento ordine
                $updateData = ['status' => 'paid'];
                $updatedOrder = $this->orderService->updateOrder($orderId, $updateData);
                $results['update_order'] = $updatedOrder ? 'success' : 'failed';
                
                // Test 3: Recupero ordine
                $retrievedOrder = $this->orderService->getOrder($orderId);
                $results['get_order'] = $retrievedOrder ? 'success' : 'failed';
                
                // Test 4: Processamento pagamento
                $paymentResult = $this->orderService->processPayment($orderId);
                $results['process_payment'] = $paymentResult['success'] ? 'success' : 'failed';
                
                // Test 5: Invio notifica
                $notificationResult = $this->orderService->sendNotification($orderId, 'confirmation');
                $results['send_notification'] = $notificationResult['success'] ? 'success' : 'failed';
                
                // Test 6: Lista ordini
                $allOrders = $this->orderService->getAllOrders();
                $results['list_orders'] = count($allOrders) > 0 ? 'success' : 'no_data';
                
                // Test 7: Cancellazione ordine
                $cancelledOrder = $this->orderService->cancelOrder($orderId, 'Test cancellation');
                $results['cancel_order'] = $cancelledOrder ? 'success' : 'failed';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
