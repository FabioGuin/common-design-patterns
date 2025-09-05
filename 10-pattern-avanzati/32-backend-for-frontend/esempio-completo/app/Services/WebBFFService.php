<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebBFFService
{
    /**
     * Ottiene ordini ottimizzati per web
     */
    public function getOrders(array $filters = []): array
    {
        $cacheKey = 'web_orders_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            Log::info('Fetching orders for web BFF', ['filters' => $filters]);
            
            $query = Order::with(['user', 'products'])
                ->select([
                    'id', 'user_id', 'total_amount', 'status', 'created_at', 'updated_at',
                    'shipping_address', 'billing_address', 'notes', 'payment_method'
                ]);
            
            // Applica filtri
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            
            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                ->limit($filters['limit'] ?? 50)
                ->get();
            
            return $this->transformOrdersForWeb($orders);
        });
    }

    /**
     * Ottiene un ordine specifico per web
     */
    public function getOrder(int $orderId): array
    {
        $cacheKey = "web_order_{$orderId}";
        
        return Cache::remember($cacheKey, 600, function () use ($orderId) {
            Log::info('Fetching order for web BFF', ['order_id' => $orderId]);
            
            $order = Order::with([
                'user:id,name,email,phone',
                'products:id,name,price,description,image_url',
                'orderItems:order_id,product_id,quantity,price'
            ])->findOrFail($orderId);
            
            return $this->transformOrderForWeb($order);
        });
    }

    /**
     * Ottiene dashboard dati per web
     */
    public function getDashboardData(): array
    {
        $cacheKey = 'web_dashboard_data';
        
        return Cache::remember($cacheKey, 60, function () {
            Log::info('Fetching dashboard data for web BFF');
            
            return [
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'recent_orders' => $this->getRecentOrders(10),
                'top_products' => $this->getTopProducts(5),
                'user_stats' => $this->getUserStats(),
                'revenue_chart' => $this->getRevenueChartData()
            ];
        });
    }

    /**
     * Ottiene prodotti ottimizzati per web
     */
    public function getProducts(array $filters = []): array
    {
        $cacheKey = 'web_products_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 600, function () use ($filters) {
            Log::info('Fetching products for web BFF', ['filters' => $filters]);
            
            $query = Product::select([
                'id', 'name', 'description', 'price', 'image_url', 'category',
                'stock_quantity', 'is_active', 'created_at', 'updated_at'
            ]);
            
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }
            
            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            }
            
            $products = $query->orderBy('name')
                ->limit($filters['limit'] ?? 100)
                ->get();
            
            return $this->transformProductsForWeb($products);
        });
    }

    /**
     * Trasforma ordini per web
     */
    private function transformOrdersForWeb($orders): array
    {
        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email
                ],
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'shipping_address' => $order->shipping_address,
                'billing_address' => $order->billing_address,
                'payment_method' => $order->payment_method,
                'products_count' => $order->products->count(),
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image_url' => $product->image_url
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Trasforma un ordine per web
     */
    private function transformOrderForWeb($order): array
    {
        return [
            'id' => $order->id,
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone
            ],
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'payment_method' => $order->payment_method,
            'notes' => $order->notes,
            'products' => $order->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'quantity' => $product->pivot->quantity ?? 1
                ];
            })
        ];
    }

    /**
     * Trasforma prodotti per web
     */
    private function transformProductsForWeb($products): array
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'category' => $product->category,
                'stock_quantity' => $product->stock_quantity,
                'is_active' => $product->is_active,
                'created_at' => $product->created_at->toISOString()
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
     * Ottiene prodotti top
     */
    private function getTopProducts(int $limit): array
    {
        return Product::select('id', 'name', 'price', 'image_url')
            ->orderBy('sales_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene statistiche utenti
     */
    private function getUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count()
        ];
    }

    /**
     * Ottiene dati per il grafico dei ricavi
     */
    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
            
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'revenue' => $revenue
            ];
        }
        
        return $data;
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
        Cache::forget('web_orders_*');
        Cache::forget('web_order_*');
        Cache::forget('web_dashboard_data');
        Cache::forget('web_products_*');
        
        Log::info('Web BFF cache cleared');
    }
}
