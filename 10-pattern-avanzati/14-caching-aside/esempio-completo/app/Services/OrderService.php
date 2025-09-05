<?php

namespace App\Services;

use App\Cache\CacheManager;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private CacheManager $cacheManager
    ) {}

    public function getOrder(int $orderId): ?array
    {
        return $this->cacheManager->get(
            (string) $orderId,
            'orders',
            function () use ($orderId) {
                return $this->loadOrderFromDatabase($orderId);
            }
        );
    }

    public function getAllOrders(): array
    {
        return $this->cacheManager->get(
            'all_orders',
            'orders',
            function () {
                return $this->loadAllOrdersFromDatabase();
            }
        );
    }

    public function getOrdersByUser(int $userId): array
    {
        return $this->cacheManager->get(
            "orders_user_{$userId}",
            'orders',
            function () use ($userId) {
                return $this->loadOrdersByUserFromDatabase($userId);
            }
        );
    }

    public function getOrdersByStatus(string $status): array
    {
        return $this->cacheManager->get(
            "orders_status_{$status}",
            'orders',
            function () use ($status) {
                return $this->loadOrdersByStatusFromDatabase($status);
            }
        );
    }

    public function createOrder(array $orderData): array
    {
        $order = Order::create($orderData);
        
        // Invalida cache correlata
        $this->invalidateRelatedCache();
        
        return $order->toArray();
    }

    public function updateOrder(int $orderId, array $orderData): array
    {
        $order = Order::findOrFail($orderId);
        $order->update($orderData);
        
        // Invalida cache specifica
        $this->cacheManager->forget((string) $orderId, 'orders');
        $this->invalidateRelatedCache();
        
        return $order->toArray();
    }

    public function deleteOrder(int $orderId): bool
    {
        $order = Order::findOrFail($orderId);
        $result = $order->delete();
        
        // Invalida cache
        $this->cacheManager->forget((string) $orderId, 'orders');
        $this->invalidateRelatedCache();
        
        return $result;
    }

    public function refreshOrder(int $orderId): ?array
    {
        return $this->cacheManager->refresh(
            (string) $orderId,
            'orders',
            function () use ($orderId) {
                return $this->loadOrderFromDatabase($orderId);
            }
        );
    }

    public function preloadOrders(): array
    {
        return $this->cacheManager->preload('orders', function () {
            return $this->loadAllOrdersFromDatabase();
        });
    }

    private function loadOrderFromDatabase(int $orderId): ?array
    {
        $order = Order::find($orderId);
        return $order ? $order->toArray() : null;
    }

    private function loadAllOrdersFromDatabase(): array
    {
        return Order::all()->toArray();
    }

    private function loadOrdersByUserFromDatabase(int $userId): array
    {
        return Order::where('user_id', $userId)->get()->toArray();
    }

    private function loadOrdersByStatusFromDatabase(string $status): array
    {
        return Order::where('status', $status)->get()->toArray();
    }

    private function invalidateRelatedCache(): void
    {
        // Invalida cache per tutti gli utenti
        $userIds = Order::distinct()->pluck('user_id');
        foreach ($userIds as $userId) {
            $this->cacheManager->forget("orders_user_{$userId}", 'orders');
        }
        
        // Invalida cache per tutti gli status
        $statuses = Order::distinct()->pluck('status');
        foreach ($statuses as $status) {
            $this->cacheManager->forget("orders_status_{$status}", 'orders');
        }
        
        // Invalida cache per tutti gli ordini
        $this->cacheManager->forget('all_orders', 'orders');
    }

    public function getCacheStats(): array
    {
        return $this->cacheManager->getCacheStats('orders');
    }
}
