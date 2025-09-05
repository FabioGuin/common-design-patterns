<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Sharding\ShardingManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShardingController extends Controller
{
    public function __construct(
        private ShardingManager $shardingManager
    ) {}

    public function index()
    {
        return view('sharding.index');
    }

    public function createUser(Request $request): JsonResponse
    {
        try {
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', 'password')),
                'status' => 'active',
            ];

            $user = new User($userData);
            $user->save();

            $shard = $this->shardingManager->getShardForKey('users', $user->id);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createProduct(Request $request): JsonResponse
    {
        try {
            $productData = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'category' => $request->input('category'),
                'status' => 'active',
            ];

            $product = new Product($productData);
            $product->save();

            $shard = $this->shardingManager->getShardForKey('products', $product->category);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createOrder(Request $request): JsonResponse
    {
        try {
            $orderData = [
                'user_id' => $request->input('user_id'),
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'total_amount' => $request->input('total_amount'),
                'status' => 'pending',
                'order_date' => now(),
            ];

            $order = new Order($orderData);
            $order->save();

            $shard = $this->shardingManager->getShardForKey('orders', $order->order_date->format('Y-m-d'));

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getUser(int $userId): JsonResponse
    {
        try {
            $user = User::findByShard($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $shard = $this->shardingManager->getShardForKey('users', $userId);

            return response()->json([
                'success' => true,
                'user' => $user,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getProduct(int $productId): JsonResponse
    {
        try {
            $product = Product::findByShard($productId);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $shard = $this->shardingManager->getShardForKey('products', $product->category);

            return response()->json([
                'success' => true,
                'product' => $product,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getOrder(int $orderId): JsonResponse
    {
        try {
            $order = Order::findByShard($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $shard = $this->shardingManager->getShardForKey('orders', $order->order_date->format('Y-m-d'));

            return response()->json([
                'success' => true,
                'order' => $order,
                'shard' => $shard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllUsers(): JsonResponse
    {
        try {
            $users = User::getAllUsers();

            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllProducts(): JsonResponse
    {
        try {
            $products = Product::getAllProducts();

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => count($products)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllOrders(): JsonResponse
    {
        try {
            $orders = Order::getAllOrders();

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getShardingStatus(string $entity): JsonResponse
    {
        try {
            $status = $this->shardingManager->getShardingStatus($entity);

            return response()->json([
                'success' => true,
                'entity' => $entity,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllShardingStatus(): JsonResponse
    {
        try {
            $entities = ['users', 'products', 'orders', 'categories'];
            $status = [];

            foreach ($entities as $entity) {
                $status[$entity] = $this->shardingManager->getShardingStatus($entity);
            }

            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMetrics(): JsonResponse
    {
        try {
            $metrics = \App\Models\ShardingMetric::orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->groupBy('entity')
                ->map(function ($entityMetrics) {
                    return [
                        'total_queries' => $entityMetrics->sum('total_queries'),
                        'successful_queries' => $entityMetrics->sum('successful_queries'),
                        'failed_queries' => $entityMetrics->sum('failed_queries'),
                        'success_rate' => $this->calculateSuccessRate($entityMetrics),
                        'avg_execution_time' => $entityMetrics->avg('execution_time'),
                        'avg_memory_used' => $entityMetrics->avg('memory_used'),
                        'last_updated' => $entityMetrics->first()->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateSuccessRate($metrics): float
    {
        $totalQueries = $metrics->sum('total_queries');
        $successfulQueries = $metrics->sum('successful_queries');

        return $totalQueries > 0 ? ($successfulQueries / $totalQueries) * 100 : 0;
    }
}
