<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MobileBFFService
{
    /**
     * Ottiene ordini ottimizzati per mobile
     */
    public function getOrders(array $filters = []): array
    {
        $cacheKey = 'mobile_orders_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 180, function () use ($filters) {
            Log::info('Fetching orders for mobile BFF', ['filters' => $filters]);
            
            $query = Order::with(['user:id,name'])
                ->select([
                    'id', 'user_id', 'total_amount', 'status', 'created_at'
                ]);
            
            // Applica filtri
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                ->limit($filters['limit'] ?? 20)
                ->get();
            
            return $this->transformOrdersForMobile($orders);
        });
    }

    /**
     * Ottiene un ordine specifico per mobile
     */
    public function getOrder(int $orderId): array
    {
        $cacheKey = "mobile_order_{$orderId}";
        
        return Cache::remember($cacheKey, 300, function () use ($orderId) {
            Log::info('Fetching order for mobile BFF', ['order_id' => $orderId]);
            
            $order = Order::with([
                'user:id,name,email',
                'products:id,name,price,image_url'
            ])->findOrFail($orderId);
            
            return $this->transformOrderForMobile($order);
        });
    }

    /**
     * Ottiene dashboard dati per mobile
     */
    public function getDashboardData(): array
    {
        $cacheKey = 'mobile_dashboard_data';
        
        return Cache::remember($cacheKey, 30, function () {
            Log::info('Fetching dashboard data for mobile BFF');
            
            return [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'recent_orders' => $this->getRecentOrders(5),
                'quick_stats' => $this->getQuickStats()
            ];
        });
    }

    /**
     * Ottiene prodotti ottimizzati per mobile
     */
    public function getProducts(array $filters = []): array
    {
        $cacheKey = 'mobile_products_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            Log::info('Fetching products for mobile BFF', ['filters' => $filters]);
            
            $query = Product::select([
                'id', 'name', 'price', 'image_url', 'category'
            ])->where('is_active', true);
            
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }
            
            $products = $query->orderBy('name')
                ->limit($filters['limit'] ?? 50)
                ->get();
            
            return $this->transformProductsForMobile($products);
        });
    }

    /**
     * Ottiene dati offline per mobile
     */
    public function getOfflineData(): array
    {
        $cacheKey = 'mobile_offline_data';
        
        return Cache::remember($cacheKey, 3600, function () {
            Log::info('Fetching offline data for mobile BFF');
            
            return [
                'categories' => $this->getCategories(),
                'products' => $this->getProducts(['limit' => 100]),
                'user_preferences' => $this->getUserPreferences()
            ];
        });
    }

    /**
     * Trasforma ordini per mobile
     */
    private function transformOrdersForMobile($orders): array
    {
        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_name' => $order->user->name,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'created_at' => $order->created_at->toISOString(),
                'formatted_amount' => '€' . number_format($order->total_amount, 2)
            ];
        })->toArray();
    }

    /**
     * Trasforma un ordine per mobile
     */
    private function transformOrderForMobile($order): array
    {
        return [
            'id' => $order->id,
            'user' => [
                'name' => $order->user->name,
                'email' => $order->user->email
            ],
            'total_amount' => $order->total_amount,
            'formatted_amount' => '€' . number_format($order->total_amount, 2),
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'created_at' => $order->created_at->toISOString(),
            'products' => $order->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url
                ];
            })
        ];
    }

    /**
     * Trasforma prodotti per mobile
     */
    private function transformProductsForMobile($products): array
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'formatted_price' => '€' . number_format($product->price, 2),
                'image_url' => $product->image_url,
                'category' => $product->category
            ];
        })->toArray();
    }

    /**
     * Ottiene ordini recenti
     */
    private function getRecentOrders(int $limit): array
    {
        return Order::with('user:id,name')
            ->select('id', 'user_id', 'total_amount', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user_name' => $order->user->name,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString()
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene statistiche rapide
     */
    private function getQuickStats(): array
    {
        return [
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'this_week_orders' => Order::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'pending_orders' => Order::where('status', 'pending')->count()
        ];
    }

    /**
     * Ottiene categorie
     */
    private function getCategories(): array
    {
        return Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->toArray();
    }

    /**
     * Ottiene preferenze utente
     */
    private function getUserPreferences(): array
    {
        return [
            'default_currency' => 'EUR',
            'date_format' => 'DD/MM/YYYY',
            'timezone' => 'Europe/Rome'
        ];
    }

    /**
     * Ottiene etichetta status
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'In Attesa',
            'processing' => 'In Elaborazione',
            'shipped' => 'Spedito',
            'completed' => 'Completato',
            'cancelled' => 'Cancellato'
        ];
        
        return $labels[$status] ?? $status;
    }

    /**
     * Pulisce la cache
     */
    public function clearCache(): void
    {
        Cache::forget('mobile_orders_*');
        Cache::forget('mobile_order_*');
        Cache::forget('mobile_dashboard_data');
        Cache::forget('mobile_products_*');
        Cache::forget('mobile_offline_data');
        
        Log::info('Mobile BFF cache cleared');
    }
}
