<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SharedDatabaseService;
use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test per il Shared Database Anti-pattern
 * 
 * Questi test dimostrano i problemi del pattern e verificano
 * che i servizi funzionino correttamente nonostante i limiti.
 */
class SharedDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private SharedDatabaseService $sharedDb;
    private UserService $userService;
    private ProductService $productService;
    private OrderService $orderService;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->sharedDb = new SharedDatabaseService();
        $this->userService = new UserService($this->sharedDb);
        $this->productService = new ProductService($this->sharedDb);
        $this->orderService = new OrderService($this->sharedDb);
        $this->paymentService = new PaymentService($this->sharedDb);
    }

    /**
     * Test creazione utente
     */
    public function test_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ];

        $user = $this->userService->createUser($userData);

        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertEquals('shared_database', $user['database']);
        $this->assertEquals('users', $user['table']);
    }

    /**
     * Test creazione prodotto
     */
    public function test_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ];

        $product = $this->productService->createProduct($productData);

        $this->assertIsArray($product);
        $this->assertEquals('Test Product', $product['name']);
        $this->assertEquals(29.99, $product['price']);
        $this->assertEquals('shared_database', $product['database']);
        $this->assertEquals('products', $product['table']);
    }

    /**
     * Test creazione ordine
     */
    public function test_can_create_order()
    {
        // Prima crea un utente
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $orderData = [
            'user_id' => $user['id'],
            'total' => 59.98,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 29.99
                ]
            ]
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertIsArray($order);
        $this->assertEquals($user['id'], $order['user_id']);
        $this->assertEquals(59.98, $order['total']);
        $this->assertEquals('shared_database', $order['database']);
        $this->assertEquals('orders', $order['table']);
    }

    /**
     * Test creazione pagamento
     */
    public function test_can_create_payment()
    {
        // Prima crea un utente e un ordine
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $order = $this->orderService->createOrder([
            'user_id' => $user['id'],
            'total' => 59.98,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 29.99
                ]
            ]
        ]);

        $paymentData = [
            'order_id' => $order['id'],
            'user_id' => $user['id'],
            'amount' => 59.98,
            'method' => 'credit_card'
        ];

        $payment = $this->paymentService->createPayment($paymentData);

        $this->assertIsArray($payment);
        $this->assertEquals($order['id'], $payment['order_id']);
        $this->assertEquals($user['id'], $payment['user_id']);
        $this->assertEquals(59.98, $payment['amount']);
        $this->assertEquals('shared_database', $payment['database']);
        $this->assertEquals('payments', $payment['table']);
    }

    /**
     * Test processamento pagamento
     */
    public function test_can_process_payment()
    {
        // Prima crea un utente, ordine e pagamento
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $order = $this->orderService->createOrder([
            'user_id' => $user['id'],
            'total' => 59.98,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 29.99
                ]
            ]
        ]);

        $payment = $this->paymentService->createPayment([
            'order_id' => $order['id'],
            'user_id' => $user['id'],
            'amount' => 59.98,
            'method' => 'credit_card'
        ]);

        $processedPayment = $this->paymentService->processPayment($payment['id']);

        $this->assertIsArray($processedPayment);
        $this->assertContains($processedPayment['status'], ['completed', 'failed']);
        $this->assertEquals('shared_database', $processedPayment['database']);
    }

    /**
     * Test aggiornamento inventario
     */
    public function test_can_update_inventory()
    {
        $product = $this->productService->createProduct([
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 29.99,
            'category' => 'Electronics',
            'inventory' => 10
        ]);

        $updatedProduct = $this->productService->updateInventory($product['id'], -2);

        $this->assertIsArray($updatedProduct);
        $this->assertEquals(8, $updatedProduct['inventory']);
        $this->assertEquals(-2, $updatedProduct['quantity_change']);
    }

    /**
     * Test aggiornamento stato ordine
     */
    public function test_can_update_order_status()
    {
        $user = $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $order = $this->orderService->createOrder([
            'user_id' => $user['id'],
            'total' => 59.98,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 29.99
                ]
            ]
        ]);

        $updatedOrder = $this->orderService->updateOrderStatus($order['id'], 'paid');

        $this->assertIsArray($updatedOrder);
        $this->assertEquals('paid', $updatedOrder['status']);
    }

    /**
     * Test simulazione deadlock
     */
    public function test_can_simulate_deadlock()
    {
        $deadlock = $this->sharedDb->simulateDeadlock();

        $this->assertIsArray($deadlock);
        $this->assertArrayHasKey('id', $deadlock);
        $this->assertArrayHasKey('error', $deadlock);
        $this->assertStringContains('Deadlock detected', $deadlock['error']);
    }

    /**
     * Test transazione distribuita complessa
     */
    public function test_can_execute_complex_transaction()
    {
        $operations = [
            [
                'table' => 'users',
                'operation' => 'read',
                'data' => ['id' => 1]
            ],
            [
                'table' => 'products',
                'operation' => 'read',
                'data' => ['id' => 1]
            ],
            [
                'table' => 'orders',
                'operation' => 'write',
                'data' => ['user_id' => 1, 'total' => 100.00]
            ]
        ];

        $result = $this->sharedDb->executeDistributedTransaction($operations);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('results', $result);
    }

    /**
     * Test statistiche servizi
     */
    public function test_can_get_service_stats()
    {
        $userStats = $this->userService->getStats();
        $productStats = $this->productService->getStats();
        $orderStats = $this->orderService->getStats();
        $paymentStats = $this->paymentService->getStats();

        $this->assertIsArray($userStats);
        $this->assertArrayHasKey('service', $userStats);
        $this->assertEquals('UserService', $userStats['service']);

        $this->assertIsArray($productStats);
        $this->assertArrayHasKey('service', $productStats);
        $this->assertEquals('ProductService', $productStats['service']);

        $this->assertIsArray($orderStats);
        $this->assertArrayHasKey('service', $orderStats);
        $this->assertEquals('OrderService', $orderStats['service']);

        $this->assertIsArray($paymentStats);
        $this->assertArrayHasKey('service', $paymentStats);
        $this->assertEquals('PaymentService', $paymentStats['service']);
    }

    /**
     * Test statistiche database condiviso
     */
    public function test_can_get_shared_database_stats()
    {
        $stats = $this->sharedDb->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('id', $stats);
        $this->assertArrayHasKey('database', $stats);
        $this->assertEquals('shared_database', $stats['database']);
        $this->assertArrayHasKey('total_operations', $stats);
        $this->assertArrayHasKey('failed_operations', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
    }

    /**
     * Test cronologia conflitti
     */
    public function test_can_get_conflict_history()
    {
        // Simula alcuni conflitti
        $this->sharedDb->simulateDeadlock();
        $this->sharedDb->simulateDeadlock();

        $conflicts = $this->sharedDb->getConflictHistory();

        $this->assertIsArray($conflicts);
        $this->assertGreaterThanOrEqual(2, count($conflicts));
    }

    /**
     * Test cronologia lock
     */
    public function test_can_get_lock_history()
    {
        // Esegui alcune operazioni per generare lock
        $this->userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $locks = $this->sharedDb->getLockHistory();

        $this->assertIsArray($locks);
    }

    /**
     * Test problemi di scalabilità
     */
    public function test_scalability_issues()
    {
        $userStats = $this->userService->getStats();
        $productStats = $this->productService->getStats();
        $orderStats = $this->orderService->getStats();
        $paymentStats = $this->paymentService->getStats();

        // Verifica che tutti i servizi abbiano problemi di scalabilità
        $this->assertTrue($userStats['scalability_issues']['shared_database']);
        $this->assertTrue($userStats['scalability_issues']['table_locks']);
        $this->assertTrue($userStats['scalability_issues']['schema_dependencies']);

        $this->assertTrue($productStats['scalability_issues']['shared_database']);
        $this->assertTrue($productStats['scalability_issues']['table_locks']);
        $this->assertTrue($productStats['scalability_issues']['schema_dependencies']);

        $this->assertTrue($orderStats['scalability_issues']['shared_database']);
        $this->assertTrue($orderStats['scalability_issues']['table_locks']);
        $this->assertTrue($orderStats['scalability_issues']['schema_dependencies']);
        $this->assertTrue($orderStats['scalability_issues']['complex_transactions']);

        $this->assertTrue($paymentStats['scalability_issues']['shared_database']);
        $this->assertTrue($paymentStats['scalability_issues']['table_locks']);
        $this->assertTrue($paymentStats['scalability_issues']['schema_dependencies']);
        $this->assertTrue($paymentStats['scalability_issues']['complex_transactions']);
    }

    /**
     * Test accoppiamento forte
     */
    public function test_strong_coupling()
    {
        $userStats = $this->userService->getStats();
        $productStats = $this->productService->getStats();
        $orderStats = $this->orderService->getStats();
        $paymentStats = $this->paymentService->getStats();

        // Verifica che tutti i servizi abbiano alto accoppiamento
        $this->assertEquals('high', $userStats['coupling_level']);
        $this->assertEquals('high', $productStats['coupling_level']);
        $this->assertEquals('high', $orderStats['coupling_level']);
        $this->assertEquals('high', $paymentStats['coupling_level']);
    }

    /**
     * Test pulizia risorse
     */
    public function test_can_cleanup_resources()
    {
        $this->sharedDb->cleanup();

        $stats = $this->sharedDb->getStats();
        $this->assertEquals(0, $stats['active_locks']);
    }
}
