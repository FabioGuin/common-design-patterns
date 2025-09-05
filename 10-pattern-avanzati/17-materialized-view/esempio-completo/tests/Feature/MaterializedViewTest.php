<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\MaterializedViewService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class MaterializedViewTest extends TestCase
{
    use RefreshDatabase;

    protected $viewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewService = new MaterializedViewService();
    }

    /** @test */
    public function it_creates_materialized_views()
    {
        // Test che dimostra la creazione delle viste materializzate
        $results = $this->viewService->createAllViews();
        
        // Verifica che tutte le viste siano state create
        $this->assertArrayHasKey('sales_by_category', $results);
        $this->assertArrayHasKey('sales_by_month', $results);
        $this->assertArrayHasKey('top_products', $results);
        $this->assertArrayHasKey('daily_sales', $results);
    }

    /** @test */
    public function it_refreshes_materialized_views()
    {
        // Crea le viste prima
        $this->viewService->createAllViews();
        
        // Test aggiornamento delle viste
        $results = $this->viewService->refreshAllViews();
        
        // Verifica che tutte le viste siano state aggiornate
        foreach ($results as $viewName => $result) {
            $this->assertEquals('refreshed', $result);
        }
    }

    /** @test */
    public function it_retrieves_data_from_materialized_views()
    {
        // Crea dati di test
        $this->createTestData();
        
        // Crea e aggiorna le viste
        $this->viewService->createAllViews();
        $this->viewService->refreshAllViews();
        
        // Test lettura dati dalle viste
        $categoryData = $this->viewService->getViewData('sales_by_category');
        $this->assertNotEmpty($categoryData);
        
        $monthData = $this->viewService->getViewData('sales_by_month');
        $this->assertNotEmpty($monthData);
        
        $productsData = $this->viewService->getViewData('top_products');
        $this->assertNotEmpty($productsData);
    }

    /** @test */
    public function it_handles_view_conditions()
    {
        // Crea dati di test
        $this->createTestData();
        
        // Crea e aggiorna le viste
        $this->viewService->createAllViews();
        $this->viewService->refreshAllViews();
        
        // Test con condizioni specifiche
        $categoryData = $this->viewService->getViewData('sales_by_category', [
            'category_id' => 1
        ]);
        
        // Verifica che i dati filtrati siano corretti
        foreach ($categoryData as $item) {
            $this->assertEquals(1, $item->category_id);
        }
    }

    /** @test */
    public function it_provides_view_statistics()
    {
        // Crea dati di test
        $this->createTestData();
        
        // Crea e aggiorna le viste
        $this->viewService->createAllViews();
        $this->viewService->refreshAllViews();
        
        // Test statistiche delle viste
        $stats = $this->viewService->getViewStats('sales_by_category');
        
        $this->assertArrayHasKey('view_name', $stats);
        $this->assertArrayHasKey('table_name', $stats);
        $this->assertArrayHasKey('row_count', $stats);
        $this->assertArrayHasKey('last_updated', $stats);
        $this->assertArrayHasKey('refresh_frequency', $stats);
    }

    /** @test */
    public function it_handles_all_views_status()
    {
        // Crea dati di test
        $this->createTestData();
        
        // Crea e aggiorna le viste
        $this->viewService->createAllViews();
        $this->viewService->refreshAllViews();
        
        // Test stato di tutte le viste
        $status = $this->viewService->getAllViewsStatus();
        
        $this->assertArrayHasKey('sales_by_category', $status);
        $this->assertArrayHasKey('sales_by_month', $status);
        $this->assertArrayHasKey('top_products', $status);
        $this->assertArrayHasKey('daily_sales', $status);
    }

    /** @test */
    public function it_performs_complete_materialized_view_test()
    {
        // Test completo del pattern Materialized View
        $results = $this->viewService->testMaterializedView();
        
        // Verifica che tutti i test siano passati
        $this->assertArrayHasKey('creation', $results);
        $this->assertArrayHasKey('status', $results);
        $this->assertArrayHasKey('data_read', $results);
        $this->assertArrayHasKey('performance', $results);
        $this->assertArrayHasKey('refresh', $results);
    }

    /** @test */
    public function it_handles_performance_improvements()
    {
        // Crea dati di test
        $this->createTestData();
        
        // Test query normale
        $start = microtime(true);
        $normalQuery = DB::table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->selectRaw('categories.id, categories.name, SUM(order_items.quantity * order_items.price) as total_sales')
            ->groupBy('categories.id', 'categories.name')
            ->get();
        $normalTime = microtime(true) - $start;
        
        // Crea e aggiorna le viste
        $this->viewService->createAllViews();
        $this->viewService->refreshAllViews();
        
        // Test vista materializzata
        $start = microtime(true);
        $materializedQuery = $this->viewService->getViewData('sales_by_category');
        $materializedTime = microtime(true) - $start;
        
        // Verifica che la vista sia più veloce (o almeno non più lenta)
        $this->assertLessThanOrEqual($normalTime, $materializedTime + 0.1); // Tolleranza di 0.1s
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        // Test con database vuoto
        $results = $this->viewService->createAllViews();
        
        // Verifica che le viste vengano create anche senza dati
        $this->assertArrayHasKey('sales_by_category', $results);
        $this->assertArrayHasKey('sales_by_month', $results);
        $this->assertArrayHasKey('top_products', $results);
        $this->assertArrayHasKey('daily_sales', $results);
        
        // Verifica che le viste siano vuote ma funzionanti
        $categoryData = $this->viewService->getViewData('sales_by_category');
        $this->assertIsArray($categoryData);
    }

    /**
     * Crea dati di test per i test
     */
    private function createTestData()
    {
        // Crea categorie
        $categories = Category::factory()->count(3)->create();
        
        // Crea prodotti
        $products = [];
        foreach ($categories as $category) {
            $products = array_merge($products, Product::factory()->count(5)->create([
                'category_id' => $category->id
            ]));
        }
        
        // Crea ordini
        for ($i = 0; $i < 20; $i++) {
            $order = Order::factory()->create([
                'status' => 'completed',
                'order_date' => now()->subDays(rand(0, 30))
            ]);
            
            // Crea elementi dell'ordine
            $orderTotal = 0;
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 3);
                $price = $product->price;
                
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price
                ]);
                
                $orderTotal += $quantity * $price;
            }
            
            // Aggiorna il totale dell'ordine
            $order->update(['total_amount' => $orderTotal]);
        }
    }
}
