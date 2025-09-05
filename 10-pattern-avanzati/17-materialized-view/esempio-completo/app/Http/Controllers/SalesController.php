<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MaterializedViewService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class SalesController extends Controller
{
    protected $viewService;

    public function __construct(MaterializedViewService $viewService)
    {
        $this->viewService = $viewService;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('materialized-view.example');
    }

    /**
     * Test del pattern Materialized View
     */
    public function test()
    {
        try {
            $results = $this->viewService->testMaterializedView();
            
            return response()->json([
                'success' => true,
                'message' => 'Test Materialized View completato',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report vendite per categoria
     */
    public function salesByCategory(Request $request)
    {
        try {
            $conditions = [];
            
            if ($request->has('category_id')) {
                $conditions['category_id'] = $request->category_id;
            }
            
            $data = $this->viewService->getViewData('sales_by_category', $conditions);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report vendite per mese
     */
    public function salesByMonth(Request $request)
    {
        try {
            $conditions = [];
            
            if ($request->has('year')) {
                $conditions['year'] = $request->year;
            }
            
            if ($request->has('month')) {
                $conditions['month'] = $request->month;
            }
            
            $data = $this->viewService->getViewData('sales_by_month', $conditions);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Top prodotti
     */
    public function topProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $conditions = [];
            
            if ($request->has('min_sales')) {
                $conditions['total_sales'] = ['>', $request->min_sales];
            }
            
            $data = $this->viewService->getViewData('top_products', $conditions);
            
            // Applica il limite
            if ($limit > 0) {
                $data = $data->take($limit);
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vendite giornaliere
     */
    public function dailySales(Request $request)
    {
        try {
            $conditions = [];
            
            if ($request->has('start_date')) {
                $conditions['sale_date'] = ['>=', $request->start_date];
            }
            
            if ($request->has('end_date')) {
                $conditions['sale_date'] = ['<=', $request->end_date];
            }
            
            $data = $this->viewService->getViewData('daily_sales', $conditions);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dati: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggiorna tutte le viste materializzate
     */
    public function refreshViews()
    {
        try {
            $results = $this->viewService->refreshAllViews();
            
            $successCount = count(array_filter($results, fn($result) => $result === 'refreshed'));
            $totalCount = count($results);
            
            return response()->json([
                'success' => true,
                'message' => "Viste aggiornate: {$successCount}/{$totalCount}",
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stato delle viste materializzate
     */
    public function viewStatus()
    {
        try {
            $status = $this->viewService->getAllViewsStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dello stato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confronto performance tra query normali e viste materializzate
     */
    public function performanceComparison()
    {
        try {
            $results = [];
            
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
            $results['normal_query_time'] = microtime(true) - $start;
            $results['normal_query_count'] = count($normalQuery);
            
            // Test vista materializzata
            $start = microtime(true);
            $materializedQuery = $this->viewService->getViewData('sales_by_category');
            $results['materialized_query_time'] = microtime(true) - $start;
            $results['materialized_query_count'] = count($materializedQuery);
            
            // Calcola il miglioramento
            $improvement = (($results['normal_query_time'] - $results['materialized_query_time']) / $results['normal_query_time']) * 100;
            $results['improvement_percentage'] = round($improvement, 2);
            
            return response()->json([
                'success' => true,
                'message' => 'Confronto performance completato',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel confronto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea dati di test per dimostrare il pattern
     */
    public function createTestData()
    {
        try {
            // Crea categorie
            $categories = [
                ['name' => 'Elettronica', 'description' => 'Prodotti elettronici'],
                ['name' => 'Abbigliamento', 'description' => 'Vestiti e accessori'],
                ['name' => 'Casa e Giardino', 'description' => 'Arredamento e giardinaggio'],
                ['name' => 'Sport', 'description' => 'Attrezzature sportive'],
                ['name' => 'Libri', 'description' => 'Libri e pubblicazioni']
            ];
            
            $createdCategories = [];
            foreach ($categories as $categoryData) {
                $category = Category::create($categoryData);
                $createdCategories[] = $category;
            }
            
            // Crea prodotti
            $products = [];
            foreach ($createdCategories as $category) {
                for ($i = 1; $i <= 5; $i++) {
                    $product = Product::create([
                        'name' => "Prodotto {$i} - {$category->name}",
                        'description' => "Descrizione prodotto {$i}",
                        'price' => rand(10, 500),
                        'category_id' => $category->id,
                        'stock_quantity' => rand(0, 100),
                        'is_active' => true
                    ]);
                    $products[] = $product;
                }
            }
            
            // Crea ordini
            $orders = [];
            for ($i = 1; $i <= 100; $i++) {
                $order = Order::create([
                    'customer_name' => "Cliente {$i}",
                    'customer_email' => "cliente{$i}@example.com",
                    'total_amount' => 0, // Sarà calcolato
                    'status' => 'completed',
                    'order_date' => now()->subDays(rand(0, 365))
                ]);
                
                // Crea elementi dell'ordine
                $orderTotal = 0;
                $itemCount = rand(1, 5);
                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products[array_rand($products)];
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    
                    $orderItem = $order->orderItems()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price
                    ]);
                    
                    $orderTotal += $quantity * $price;
                }
                
                // Aggiorna il totale dell'ordine
                $order->update(['total_amount' => $orderTotal]);
                $orders[] = $order;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Dati di test creati con successo',
                'data' => [
                    'categories' => count($createdCategories),
                    'products' => count($products),
                    'orders' => count($orders)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione dei dati: ' . $e->getMessage()
            ], 500);
        }
    }
}
