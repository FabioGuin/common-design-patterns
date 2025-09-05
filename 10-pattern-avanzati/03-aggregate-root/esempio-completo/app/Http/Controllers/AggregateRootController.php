<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Aggregates\Order;
use App\ValueObjects\OrderAddress;
use App\ValueObjects\OrderPayment;
use App\Repositories\OrderRepository;

/**
 * Controller per dimostrare il Aggregate Root Pattern
 * 
 * Questo controller mostra come l'Aggregate Root Pattern
 * controlla tutte le modifiche e garantisce la consistenza
 * dei dati attraverso regole di business centralizzate.
 */
class AggregateRootController extends Controller
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Endpoint principale - mostra l'interfaccia web
     */
    public function index()
    {
        return view('aggregate_root.example');
    }

    /**
     * Endpoint di test - dimostra il pattern
     */
    public function test(Request $request): JsonResponse
    {
        $testType = $request->input('type', 'all');
        
        $results = [];
        
        switch ($testType) {
            case 'order':
                $results = $this->testOrderAggregate();
                break;
            case 'business-rules':
                $results = $this->testBusinessRules();
                break;
            case 'events':
                $results = $this->testDomainEvents();
                break;
            default:
                $results = $this->testAllScenarios();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Aggregate Root Pattern test completed',
            'data' => $results
        ]);
    }

    /**
     * Crea un nuovo ordine
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'customerId' => 'required|string'
        ]);

        try {
            $orderId = $this->orderRepository->generateId();
            $order = new Order($orderId, $request->customerId);
            
            $this->orderRepository->save($order);
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Aggiunge un item all'ordine
     */
    public function addItem(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'productId' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0'
        ]);

        try {
            $order = $this->orderRepository->findById($id);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'error' => 'Order not found'
                ], 404);
            }

            $order->addItem($request->productId, $request->quantity, $request->price);
            $this->orderRepository->save($order);
            
            return response()->json([
                'success' => true,
                'message' => 'Item added successfully',
                'data' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Conferma un ordine
     */
    public function confirmOrder(Request $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->findById($id);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'error' => 'Order not found'
                ], 404);
            }

            // Aggiungi indirizzi e pagamento se forniti
            if ($request->has('shippingAddress')) {
                $shippingAddress = new OrderAddress(
                    $request->shippingAddress['street'],
                    $request->shippingAddress['city'],
                    $request->shippingAddress['postalCode'],
                    $request->shippingAddress['country'],
                    $request->shippingAddress['state'] ?? null
                );
                $order->setShippingAddress($shippingAddress);
            }

            if ($request->has('billingAddress')) {
                $billingAddress = new OrderAddress(
                    $request->billingAddress['street'],
                    $request->billingAddress['city'],
                    $request->billingAddress['postalCode'],
                    $request->billingAddress['country'],
                    $request->billingAddress['state'] ?? null
                );
                $order->setBillingAddress($billingAddress);
            }

            if ($request->has('payment')) {
                $payment = new OrderPayment(
                    $request->payment['method'],
                    $request->payment['status'] ?? 'PENDING',
                    $request->payment['transactionId'] ?? null,
                    $request->payment['cardLastFour'] ?? null,
                    $request->payment['cardBrand'] ?? null
                );
                $order->setPayment($payment);
            }

            $order->confirm();
            $this->orderRepository->save($order);
            
            return response()->json([
                'success' => true,
                'message' => 'Order confirmed successfully',
                'data' => $order->toArray(),
                'events' => $order->getDomainEvents()->map(function ($event) {
                    return $event->toArray();
                })->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancella un ordine
     */
    public function cancelOrder(Request $request, string $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->findById($id);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'error' => 'Order not found'
                ], 404);
            }

            $order->cancel();
            $this->orderRepository->save($order);
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order->toArray(),
                'events' => $order->getDomainEvents()->map(function ($event) {
                    return $event->toArray();
                })->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Test completo dell'Order Aggregate
     */
    private function testOrderAggregate(): array
    {
        $orderId = $this->orderRepository->generateId();
        $order = new Order($orderId, 'customer-123');
        
        // Aggiungi items
        $order->addItem('PROD-001', 2, 10.50);
        $order->addItem('PROD-002', 1, 25.00);
        
        // Imposta indirizzi
        $shippingAddress = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setShippingAddress($shippingAddress);
        
        $billingAddress = new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $order->setBillingAddress($billingAddress);
        
        // Imposta pagamento
        $payment = new OrderPayment('CREDIT_CARD', 'PENDING', 'TXN-123', '1234', 'VISA');
        $order->setPayment($payment);
        
        // Conferma l'ordine
        $order->confirm();
        
        return [
            'order' => $order->toArray(),
            'canBeModified' => $order->canBeModified(),
            'canBeConfirmed' => $order->canBeConfirmed(),
            'canBeCancelled' => $order->canBeCancelled(),
            'events' => $order->getDomainEvents()->map(function ($event) {
                return $event->toArray();
            })->toArray()
        ];
    }

    /**
     * Test delle regole di business
     */
    private function testBusinessRules(): array
    {
        $results = [];
        
        // Test 1: Ordine vuoto non può essere confermato
        $order1 = new Order('order-1', 'customer-123');
        $results['empty_order_cannot_be_confirmed'] = !$order1->canBeConfirmed();
        
        // Test 2: Ordine senza indirizzi non può essere confermato
        $order2 = new Order('order-2', 'customer-123');
        $order2->addItem('PROD-001', 1, 10.00);
        $results['order_without_addresses_cannot_be_confirmed'] = !$order2->canBeConfirmed();
        
        // Test 3: Ordine confermato non può essere modificato
        $order3 = new Order('order-3', 'customer-123');
        $order3->addItem('PROD-001', 1, 10.00);
        $order3->setShippingAddress(new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT'));
        $order3->setBillingAddress(new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT'));
        $order3->confirm();
        $results['confirmed_order_cannot_be_modified'] = !$order3->canBeModified();
        
        // Test 4: Ordine confermato può essere cancellato
        $results['confirmed_order_can_be_cancelled'] = $order3->canBeCancelled();
        
        return $results;
    }

    /**
     * Test degli eventi di dominio
     */
    private function testDomainEvents(): array
    {
        $orderId = $this->orderRepository->generateId();
        $order = new Order($orderId, 'customer-123');
        
        $order->addItem('PROD-001', 1, 10.00);
        $order->setShippingAddress(new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT'));
        $order->setBillingAddress(new OrderAddress('Via Roma 123', 'Milano', '20100', 'IT'));
        
        $order->confirm();
        $confirmEvents = $order->getDomainEvents()->toArray();
        
        $order->cancel();
        $cancelEvents = $order->getDomainEvents()->toArray();
        
        return [
            'confirm_events' => $confirmEvents,
            'cancel_events' => $cancelEvents,
            'total_events' => count($confirmEvents) + count($cancelEvents)
        ];
    }

    /**
     * Test di tutti gli scenari
     */
    private function testAllScenarios(): array
    {
        return [
            'order_aggregate' => $this->testOrderAggregate(),
            'business_rules' => $this->testBusinessRules(),
            'domain_events' => $this->testDomainEvents(),
            'repository_stats' => $this->orderRepository->getStatistics()
        ];
    }
}
