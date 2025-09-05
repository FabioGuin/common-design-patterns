<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DesktopBFFService
{
    /**
     * Ottiene ordini ottimizzati per desktop
     */
    public function getOrders(array $filters = []): array
    {
        $cacheKey = 'desktop_orders_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 600, function () use ($filters) {
            Log::info('Fetching orders for desktop BFF', ['filters' => $filters]);
            
            $query = Order::with(['user', 'products', 'orderItems'])
                ->select([
                    'id', 'user_id', 'total_amount', 'status', 'created_at', 'updated_at',
                    'shipping_address', 'billing_address', 'notes', 'payment_method',
                    'tax_amount', 'discount_amount', 'shipping_cost'
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
                ->limit($filters['limit'] ?? 100)
                ->get();
            
            return $this->transformOrdersForDesktop($orders);
        });
    }

    /**
     * Ottiene un ordine specifico per desktop
     */
    public function getOrder(int $orderId): array
    {
        $cacheKey = "desktop_order_{$orderId}";
        
        return Cache::remember($cacheKey, 900, function () use ($orderId) {
            Log::info('Fetching order for desktop BFF', ['order_id' => $orderId]);
            
            $order = Order::with([
                'user:id,name,email,phone,address',
                'products:id,name,price,description,image_url,sku',
                'orderItems:order_id,product_id,quantity,price,tax_rate',
                'orderHistory:order_id,status,changed_at,changed_by'
            ])->findOrFail($orderId);
            
            return $this->transformOrderForDesktop($order);
        });
    }

    /**
     * Ottiene dashboard dati per desktop
     */
    public function getDashboardData(): array
    {
        $cacheKey = 'desktop_dashboard_data';
        
        return Cache::remember($cacheKey, 120, function () {
            Log::info('Fetching dashboard data for desktop BFF');
            
            return [
                'total_orders' => Order::count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'recent_orders' => $this->getRecentOrders(20),
                'top_products' => $this->getTopProducts(10),
                'user_stats' => $this->getUserStats(),
                'revenue_chart' => $this->getRevenueChartData(),
                'order_status_chart' => $this->getOrderStatusChartData(),
                'product_categories' => $this->getProductCategories()
            ];
        });
    }

    /**
     * Ottiene prodotti ottimizzati per desktop
     */
    public function getProducts(array $filters = []): array
    {
        $cacheKey = 'desktop_products_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 900, function () use ($filters) {
            Log::info('Fetching products for desktop BFF', ['filters' => $filters]);
            
            $query = Product::select([
                'id', 'name', 'description', 'price', 'image_url', 'category',
                'stock_quantity', 'is_active', 'sku', 'weight', 'dimensions',
                'created_at', 'updated_at', 'sales_count', 'rating'
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
                      ->orWhere('description', 'like', "%{$filters['search']}%")
                      ->orWhere('sku', 'like', "%{$filters['search']}%");
                });
            }
            
            $products = $query->orderBy('name')
                ->limit($filters['limit'] ?? 200)
                ->get();
            
            return $this->transformProductsForDesktop($products);
        });
    }

    /**
     * Ottiene dati per export
     */
    public function getExportData(string $type, array $filters = []): array
    {
        Log::info('Generating export data for desktop BFF', ['type' => $type]);
        
        switch ($type) {
            case 'orders':
                return $this->getOrdersExportData($filters);
            case 'products':
                return $this->getProductsExportData($filters);
            case 'users':
                return $this->getUsersExportData($filters);
            default:
                throw new \InvalidArgumentException("Unsupported export type: {$type}");
        }
    }

    /**
     * Trasforma ordini per desktop
     */
    private function transformOrdersForDesktop($orders): array
    {
        return $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone
                ],
                'total_amount' => $order->total_amount,
                'tax_amount' => $order->tax_amount,
                'discount_amount' => $order->discount_amount,
                'shipping_cost' => $order->shipping_cost,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'shipping_address' => $order->shipping_address,
                'billing_address' => $order->billing_address,
                'payment_method' => $order->payment_method,
                'notes' => $order->notes,
                'products_count' => $order->products->count(),
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image_url' => $product->image_url,
                        'sku' => $product->sku
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Trasforma un ordine per desktop
     */
    private function transformOrderForDesktop($order): array
    {
        return [
            'id' => $order->id,
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
                'address' => $order->user->address
            ],
            'total_amount' => $order->total_amount,
            'tax_amount' => $order->tax_amount,
            'discount_amount' => $order->discount_amount,
            'shipping_cost' => $order->shipping_cost,
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
                    'sku' => $product->sku,
                    'quantity' => $product->pivot->quantity ?? 1,
                    'tax_rate' => $product->pivot->tax_rate ?? 0
                ];
            }),
            'order_history' => $order->orderHistory ?? []
        ];
    }

    /**
     * Trasforma prodotti per desktop
     */
    private function transformProductsForDesktop($products): array
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
                'sku' => $product->sku,
                'weight' => $product->weight,
                'dimensions' => $product->dimensions,
                'sales_count' => $product->sales_count,
                'rating' => $product->rating,
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
        return Product::select('id', 'name', 'price', 'image_url', 'sales_count')
            ->orderBy('sales_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'sales_count' => $product->sales_count
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
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count()
        ];
    }

    /**
     * Ottiene dati per il grafico dei ricavi
     */
    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
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
     * Ottiene dati per il grafico degli status ordini
     */
    private function getOrderStatusChartData(): array
    {
        return Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene categorie prodotti
     */
    private function getProductCategories(): array
    {
        return Product::selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene dati export ordini
     */
    private function getOrdersExportData(array $filters): array
    {
        $orders = Order::with(['user', 'products'])
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                return $query->where('created_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                return $query->where('created_at', '<=', $filters['date_to']);
            })
            ->get();
        
        return $this->transformOrdersForDesktop($orders);
    }

    /**
     * Ottiene dati export prodotti
     */
    private function getProductsExportData(array $filters): array
    {
        $products = Product::when(isset($filters['category']), function ($query) use ($filters) {
                return $query->where('category', $filters['category']);
            })
            ->get();
        
        return $this->transformProductsForDesktop($products);
    }

    /**
     * Ottiene dati export utenti
     */
    private function getUsersExportData(array $filters): array
    {
        return User::when(isset($filters['is_active']), function ($query) use ($filters) {
                return $query->where('is_active', $filters['is_active']);
            })
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->toISOString()
                ];
            })
            ->toArray();
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
        Cache::forget('desktop_orders_*');
        Cache::forget('desktop_order_*');
        Cache::forget('desktop_dashboard_data');
        Cache::forget('desktop_products_*');
        
        Log::info('Desktop BFF cache cleared');
    }
}
