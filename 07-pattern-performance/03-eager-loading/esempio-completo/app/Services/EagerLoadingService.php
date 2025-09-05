<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EagerLoadingService
{
    private array $loadingStats = [
        'total_queries' => 0,
        'eager_loaded_queries' => 0,
        'n_plus_1_queries' => 0,
        'total_loading_time' => 0
    ];

    /**
     * Get users with eager loading
     */
    public function getUsersWithEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading users with eager loading');
        
        $users = User::with(['orders', 'profile', 'roles'])
            ->withCount(['orders', 'roles'])
            ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Users loaded with eager loading', [
            'count' => $users->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $users->toArray();
    }

    /**
     * Get users without eager loading (N+1 problem)
     */
    public function getUsersWithoutEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading users without eager loading (N+1 problem)');
        
        $users = User::all();
        
        // This will cause N+1 queries
        foreach ($users as $user) {
            $user->orders; // This triggers a query for each user
            $user->profile; // This triggers a query for each user
            $user->roles; // This triggers a query for each user
        }
        
        $endTime = microtime(true);
        $this->loadingStats['n_plus_1_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Users loaded without eager loading', [
            'count' => $users->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $users->toArray();
    }

    /**
     * Get products with eager loading
     */
    public function getProductsWithEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading products with eager loading');
        
        $products = Product::with([
            'category',
            'reviews',
            'images',
            'tags',
            'inventory'
        ])
        ->withCount(['reviews', 'images', 'tags'])
        ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Products loaded with eager loading', [
            'count' => $products->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $products->toArray();
    }

    /**
     * Get orders with eager loading
     */
    public function getOrdersWithEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading orders with eager loading');
        
        $orders = Order::with([
            'user',
            'items.product',
            'shipping',
            'payment',
            'status'
        ])
        ->withCount(['items'])
        ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Orders loaded with eager loading', [
            'count' => $orders->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $orders->toArray();
    }

    /**
     * Get categories with products
     */
    public function getCategoriesWithProducts(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading categories with products');
        
        $categories = Category::with([
            'products' => function ($query) {
                $query->with(['reviews', 'images'])
                      ->withCount(['reviews']);
            }
        ])
        ->withCount(['products'])
        ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Categories loaded with eager loading', [
            'count' => $categories->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $categories->toArray();
    }

    /**
     * Get dashboard data with eager loading
     */
    public function getDashboardData(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading dashboard data with eager loading');
        
        $data = [
            'users' => User::with(['roles'])->withCount(['orders'])->get(),
            'products' => Product::with(['category'])->withCount(['reviews'])->get(),
            'orders' => Order::with(['user'])->withCount(['items'])->get(),
            'categories' => Category::withCount(['products'])->get()
        ];
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Dashboard data loaded with eager loading', [
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return [
            'users' => $data['users']->toArray(),
            'products' => $data['products']->toArray(),
            'orders' => $data['orders']->toArray(),
            'categories' => $data['categories']->toArray()
        ];
    }

    /**
     * Get selective eager loading
     */
    public function getSelectiveEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading with selective eager loading');
        
        $users = User::select(['id', 'name', 'email', 'created_at'])
            ->with([
                'orders:id,user_id,status,total',
                'profile:id,user_id,avatar,bio'
            ])
            ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Selective eager loading completed', [
            'count' => $users->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $users->toArray();
    }

    /**
     * Get conditional eager loading
     */
    public function getConditionalEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading with conditional eager loading');
        
        $users = User::with([
            'orders' => function ($query) {
                $query->where('status', 'completed');
            },
            'profile' => function ($query) {
                $query->whereNotNull('avatar');
            }
        ])
        ->where('is_active', true)
        ->get();
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Conditional eager loading completed', [
            'count' => $users->count(),
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return $users->toArray();
    }

    /**
     * Get batch eager loading
     */
    public function getBatchEagerLoading(): array
    {
        $startTime = microtime(true);
        $this->loadingStats['total_queries']++;
        
        Log::info('Loading with batch eager loading');
        
        $users = User::with(['orders', 'profile', 'roles'])
            ->chunk(100, function ($chunk) {
                // Process each chunk
                foreach ($chunk as $user) {
                    $user->orders;
                    $user->profile;
                    $user->roles;
                }
            });
        
        $endTime = microtime(true);
        $this->loadingStats['eager_loaded_queries']++;
        $this->loadingStats['total_loading_time'] += ($endTime - $startTime);
        
        Log::info('Batch eager loading completed', [
            'loading_time' => ($endTime - $startTime) * 1000 . 'ms'
        ]);
        
        return [];
    }

    /**
     * Get loading statistics
     */
    public function getLoadingStats(): array
    {
        return array_merge($this->loadingStats, [
            'average_loading_time' => $this->loadingStats['total_queries'] > 0 
                ? $this->loadingStats['total_loading_time'] / $this->loadingStats['total_queries']
                : 0,
            'eager_loading_efficiency' => $this->loadingStats['total_queries'] > 0
                ? round(($this->loadingStats['eager_loaded_queries'] / $this->loadingStats['total_queries']) * 100, 2)
                : 0,
            'n_plus_1_problem_rate' => $this->loadingStats['total_queries'] > 0
                ? round(($this->loadingStats['n_plus_1_queries'] / $this->loadingStats['total_queries']) * 100, 2)
                : 0,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]);
    }

    /**
     * Reset loading statistics
     */
    public function resetStats(): void
    {
        $this->loadingStats = [
            'total_queries' => 0,
            'eager_loaded_queries' => 0,
            'n_plus_1_queries' => 0,
            'total_loading_time' => 0
        ];
        
        Log::info('Eager loading statistics reset');
    }

    /**
     * Get query count
     */
    public function getQueryCount(): int
    {
        return DB::getQueryLog();
    }

    /**
     * Enable query logging
     */
    public function enableQueryLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Disable query logging
     */
    public function disableQueryLogging(): void
    {
        DB::disableQueryLog();
    }
}
