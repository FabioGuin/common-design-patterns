<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LegacyController extends Controller
{
    /**
     * Gestisce le richieste per gli utenti (sistema legacy)
     */
    public function users(Request $request): JsonResponse
    {
        // Simula il sistema legacy
        $users = $this->getLegacyUsers();
        
        return response()->json([
            'system' => 'legacy',
            'feature' => 'users',
            'data' => $users,
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'legacy_notes' => 'This is the legacy user system'
        ]);
    }

    /**
     * Gestisce le richieste per i prodotti (sistema legacy)
     */
    public function products(Request $request): JsonResponse
    {
        // Simula il sistema legacy
        $products = $this->getLegacyProducts();
        
        return response()->json([
            'system' => 'legacy',
            'feature' => 'products',
            'data' => $products,
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'legacy_notes' => 'This is the legacy product system'
        ]);
    }

    /**
     * Gestisce le richieste per gli ordini (sistema legacy)
     */
    public function orders(Request $request): JsonResponse
    {
        // Simula il sistema legacy
        $orders = $this->getLegacyOrders();
        
        return response()->json([
            'system' => 'legacy',
            'feature' => 'orders',
            'data' => $orders,
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'legacy_notes' => 'This is the legacy order system'
        ]);
    }

    /**
     * Ottiene gli utenti dal sistema legacy
     */
    private function getLegacyUsers(): array
    {
        // Simula un tempo di elaborazione più lento
        usleep(mt_rand(50000, 100000)); // 50-100ms
        
        return [
            [
                'id' => 1,
                'name' => 'John Doe (Legacy)',
                'email' => 'john@legacy.com',
                'created_at' => '2020-01-01T00:00:00Z',
                'legacy_field' => 'This is a legacy-specific field'
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith (Legacy)',
                'email' => 'jane@legacy.com',
                'created_at' => '2020-01-02T00:00:00Z',
                'legacy_field' => 'Another legacy field'
            ],
            [
                'id' => 3,
                'name' => 'Bob Johnson (Legacy)',
                'email' => 'bob@legacy.com',
                'created_at' => '2020-01-03T00:00:00Z',
                'legacy_field' => 'Legacy data format'
            ]
        ];
    }

    /**
     * Ottiene i prodotti dal sistema legacy
     */
    private function getLegacyProducts(): array
    {
        // Simula un tempo di elaborazione più lento
        usleep(mt_rand(50000, 100000)); // 50-100ms
        
        return [
            [
                'id' => 1,
                'name' => 'Legacy Product 1',
                'price' => 19.99,
                'description' => 'This is a legacy product',
                'legacy_category' => 'Old Category',
                'created_at' => '2020-01-01T00:00:00Z'
            ],
            [
                'id' => 2,
                'name' => 'Legacy Product 2',
                'price' => 29.99,
                'description' => 'Another legacy product',
                'legacy_category' => 'Old Category',
                'created_at' => '2020-01-02T00:00:00Z'
            ],
            [
                'id' => 3,
                'name' => 'Legacy Product 3',
                'price' => 39.99,
                'description' => 'Third legacy product',
                'legacy_category' => 'Old Category',
                'created_at' => '2020-01-03T00:00:00Z'
            ]
        ];
    }

    /**
     * Ottiene gli ordini dal sistema legacy
     */
    private function getLegacyOrders(): array
    {
        // Simula un tempo di elaborazione più lento
        usleep(mt_rand(50000, 100000)); // 50-100ms
        
        return [
            [
                'id' => 1,
                'user_id' => 1,
                'total' => 59.98,
                'status' => 'completed',
                'legacy_order_number' => 'LEG-001',
                'created_at' => '2020-01-01T00:00:00Z'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'total' => 89.97,
                'status' => 'pending',
                'legacy_order_number' => 'LEG-002',
                'created_at' => '2020-01-02T00:00:00Z'
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'total' => 119.96,
                'status' => 'shipped',
                'legacy_order_number' => 'LEG-003',
                'created_at' => '2020-01-03T00:00:00Z'
            ]
        ];
    }

    /**
     * Simula un errore del sistema legacy
     */
    public function simulateError(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Legacy system error',
            'message' => 'This is a simulated legacy system error',
            'error_code' => 'LEGACY_ERROR_001',
            'timestamp' => now()->toISOString()
        ], 500);
    }

    /**
     * Ottiene le statistiche del sistema legacy
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'system' => 'legacy',
            'version' => '1.0.0',
            'uptime' => '365 days',
            'total_users' => 1000,
            'total_products' => 500,
            'total_orders' => 2500,
            'last_updated' => '2020-01-01T00:00:00Z',
            'legacy_metrics' => [
                'response_time_avg' => '150ms',
                'error_rate' => '2%',
                'memory_usage' => '512MB'
            ]
        ]);
    }
}
