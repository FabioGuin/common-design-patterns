<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventSourcingController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index()
    {
        return view('event-sourcing.index');
    }

    public function createOrder(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                customerId: $request->input('customer_id'),
                items: $request->input('items'),
                totalAmount: (float) $request->input('total_amount'),
                shippingAddress: $request->input('shipping_address')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine creato con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function payOrder(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->payOrder(
                orderId: $orderId,
                paymentMethod: $request->input('payment_method'),
                transactionId: $request->input('transaction_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine pagato con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function shipOrder(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->shipOrder(
                orderId: $orderId,
                trackingNumber: $request->input('tracking_number'),
                carrier: $request->input('carrier')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine spedito con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deliverOrder(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->deliverOrder(
                orderId: $orderId,
                deliveryConfirmation: $request->input('delivery_confirmation')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine consegnato con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancelOrder(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->cancelOrder(
                orderId: $orderId,
                reason: $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine cancellato con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function refundOrder(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->refundOrder(
                orderId: $orderId,
                refundAmount: (float) $request->input('refund_amount'),
                reason: $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ordine rimborsato con successo',
                'order' => $order->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getOrder(string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($orderId);
            $projection = $this->orderService->getOrderProjection($orderId);
            $events = $this->orderService->getOrderEvents($orderId);

            return response()->json([
                'success' => true,
                'order' => $order->toArray(),
                'projection' => $projection,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getAllOrders(): JsonResponse
    {
        try {
            $projections = $this->orderService->getAllOrderProjections();

            return response()->json([
                'success' => true,
                'orders' => $projections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrderEvents(string $orderId): JsonResponse
    {
        try {
            $events = $this->orderService->getOrderEvents($orderId);

            return response()->json([
                'success' => true,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getAllEvents(): JsonResponse
    {
        try {
            $events = $this->orderService->getAllEvents();

            return response()->json([
                'success' => true,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
