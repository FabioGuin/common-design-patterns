<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderService
{
    public function updateOrderStatus(array $orderData, string $status): array
    {
        $orderId = $orderData['order_id'];

        $order = Order::where('order_id', $orderId)->first();
        
        if (!$order) {
            // Crea ordine se non esiste
            $order = Order::create([
                'order_id' => $orderId,
                'customer_id' => $orderData['customer_id'] ?? 'CUST-001',
                'product_id' => $orderData['product_id'] ?? 'PROD-001',
                'quantity' => $orderData['quantity'] ?? 1,
                'total_amount' => $orderData['total_amount'] ?? 0,
                'status' => $status,
                'created_at' => now(),
            ]);
        } else {
            $order->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
        }

        return [
            'order_id' => $orderId,
            'status' => $status,
            'updated_at' => $order->updated_at
        ];
    }

    public function revertOrderStatus(array $orderData): array
    {
        $orderId = $orderData['order_id'];

        $order = Order::where('order_id', $orderId)->first();
        
        if (!$order) {
            throw new \Exception("Order not found: {$orderId}");
        }

        $order->update([
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);

        return [
            'order_id' => $orderId,
            'status' => 'cancelled',
            'reverted_at' => now()
        ];
    }

    public function createOrder(array $orderData): Order
    {
        return Order::create([
            'order_id' => $orderData['order_id'] ?? Str::uuid()->toString(),
            'customer_id' => $orderData['customer_id'] ?? 'CUST-001',
            'product_id' => $orderData['product_id'] ?? 'PROD-001',
            'quantity' => $orderData['quantity'] ?? 1,
            'total_amount' => $orderData['total_amount'] ?? 0,
            'status' => 'pending',
            'created_at' => now(),
        ]);
    }

    public function getOrder(string $orderId): ?Order
    {
        return Order::where('order_id', $orderId)->first();
    }

    public function getAllOrders(): array
    {
        return Order::orderBy('created_at', 'desc')->get()->toArray();
    }

    public function getOrdersByStatus(string $status): array
    {
        return Order::where('status', $status)->get()->toArray();
    }
}
