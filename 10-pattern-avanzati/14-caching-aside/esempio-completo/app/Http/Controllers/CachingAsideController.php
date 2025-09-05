<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\UserService;
use App\Services\OrderService;
use App\Cache\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CachingAsideController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private UserService $userService,
        private OrderService $orderService,
        private CacheManager $cacheManager
    ) {}

    public function index()
    {
        return view('caching-aside.index');
    }

    public function getProduct(int $productId): JsonResponse
    {
        try {
            $product = $this->productService->getProduct($productId);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'product' => $product
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
            $products = $this->productService->getAllProducts();

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

    public function getProductsByCategory(string $category): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($category);

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

            $product = $this->productService->createProduct($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateProduct(Request $request, int $productId): JsonResponse
    {
        try {
            $productData = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'category' => $request->input('category'),
                'status' => $request->input('status', 'active'),
            ];

            $product = $this->productService->updateProduct($productId, $productData);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteProduct(int $productId): JsonResponse
    {
        try {
            $result = $this->productService->deleteProduct($productId);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Product deleted successfully' : 'Failed to delete product'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function refreshProduct(int $productId): JsonResponse
    {
        try {
            $product = $this->productService->refreshProduct($productId);

            return response()->json([
                'success' => true,
                'message' => 'Product cache refreshed',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function preloadProducts(): JsonResponse
    {
        try {
            $preloaded = $this->productService->preloadProducts();

            return response()->json([
                'success' => true,
                'message' => 'Products preloaded successfully',
                'preloaded_count' => count($preloaded)
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
            $user = $this->userService->getUser($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $user
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
            $users = $this->userService->getAllUsers();

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

    public function getUsersByStatus(string $status): JsonResponse
    {
        try {
            $users = $this->userService->getUsersByStatus($status);

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

    public function createUser(Request $request): JsonResponse
    {
        try {
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', 'password')),
                'status' => 'active',
            ];

            $user = $this->userService->createUser($userData);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateUser(Request $request, int $userId): JsonResponse
    {
        try {
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'status' => $request->input('status', 'active'),
            ];

            $user = $this->userService->updateUser($userId, $userData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteUser(int $userId): JsonResponse
    {
        try {
            $result = $this->userService->deleteUser($userId);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'User deleted successfully' : 'Failed to delete user'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function refreshUser(int $userId): JsonResponse
    {
        try {
            $user = $this->userService->refreshUser($userId);

            return response()->json([
                'success' => true,
                'message' => 'User cache refreshed',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function preloadUsers(): JsonResponse
    {
        try {
            $preloaded = $this->userService->preloadUsers();

            return response()->json([
                'success' => true,
                'message' => 'Users preloaded successfully',
                'preloaded_count' => count($preloaded)
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
            $order = $this->orderService->getOrder($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'order' => $order
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
            $orders = $this->orderService->getAllOrders();

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

    public function getOrdersByUser(int $userId): JsonResponse
    {
        try {
            $orders = $this->orderService->getOrdersByUser($userId);

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

    public function getOrdersByStatus(string $status): JsonResponse
    {
        try {
            $orders = $this->orderService->getOrdersByStatus($status);

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

    public function createOrder(Request $request): JsonResponse
    {
        try {
            $orderData = [
                'user_id' => $request->input('user_id'),
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'total_amount' => $request->input('total_amount'),
                'status' => 'pending',
            ];

            $order = $this->orderService->createOrder($orderData);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateOrder(Request $request, int $orderId): JsonResponse
    {
        try {
            $orderData = [
                'user_id' => $request->input('user_id'),
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'total_amount' => $request->input('total_amount'),
                'status' => $request->input('status', 'pending'),
            ];

            $order = $this->orderService->updateOrder($orderId, $orderData);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteOrder(int $orderId): JsonResponse
    {
        try {
            $result = $this->orderService->deleteOrder($orderId);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Order deleted successfully' : 'Failed to delete order'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function refreshOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->refreshOrder($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Order cache refreshed',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function preloadOrders(): JsonResponse
    {
        try {
            $preloaded = $this->orderService->preloadOrders();

            return response()->json([
                'success' => true,
                'message' => 'Orders preloaded successfully',
                'preloaded_count' => count($preloaded)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getCacheStats(string $entity): JsonResponse
    {
        try {
            $stats = match ($entity) {
                'products' => $this->productService->getCacheStats(),
                'users' => $this->userService->getCacheStats(),
                'orders' => $this->orderService->getCacheStats(),
                default => throw new \InvalidArgumentException("Unknown entity: {$entity}")
            };

            return response()->json([
                'success' => true,
                'entity' => $entity,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllCacheStats(): JsonResponse
    {
        try {
            $stats = [
                'products' => $this->productService->getCacheStats(),
                'users' => $this->userService->getCacheStats(),
                'orders' => $this->orderService->getCacheStats(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
