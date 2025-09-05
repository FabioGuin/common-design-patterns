<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Services\WriteThroughService;

class ProductController extends Controller
{
    protected $writeThroughService;

    public function __construct(WriteThroughService $writeThroughService)
    {
        $this->writeThroughService = $writeThroughService;
    }

    /**
     * Mostra l'interfaccia per testare il pattern
     */
    public function index()
    {
        return view('write-through.example');
    }

    /**
     * Test del pattern Write-Through
     */
    public function test()
    {
        try {
            // Test con il service
            $serviceResults = $this->writeThroughService->testWriteThrough();
            
            // Test con il model
            $modelResults = Product::testWriteThrough();
            
            return response()->json([
                'success' => true,
                'message' => 'Test Write-Through completato',
                'service_test' => $serviceResults,
                'model_test' => $modelResults,
                'stats' => $this->writeThroughService->getStats()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuovo prodotto con Write-Through
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        try {
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prodotto creato con Write-Through',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostra un prodotto (lettura dalla cache)
     */
    public function show($id)
    {
        try {
            $product = Product::findWithCache($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prodotto non trovato'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella lettura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggiorna un prodotto con Write-Through
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0'
        ]);

        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prodotto non trovato'
                ], 404);
            }

            $product->update($request->only(['name', 'description', 'price', 'stock']));

            return response()->json([
                'success' => true,
                'message' => 'Prodotto aggiornato con Write-Through',
                'data' => $product->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'aggiornamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un prodotto con Write-Through
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prodotto non trovato'
                ], 404);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prodotto eliminato con Write-Through'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nell\'eliminazione: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista tutti i prodotti
     */
    public function list()
    {
        try {
            $products = Product::all();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella lettura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test di performance del pattern
     */
    public function performanceTest()
    {
        try {
            $iterations = 100;
            $times = [];

            // Test scrittura
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                $product = Product::create([
                    'name' => "Test Product {$i}",
                    'description' => "Test Description {$i}",
                    'price' => rand(10, 1000),
                    'stock' => rand(1, 100)
                ]);
            }
            $times['write'] = microtime(true) - $start;

            // Test lettura
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                Product::findWithCache(rand(1, $iterations));
            }
            $times['read'] = microtime(true) - $start;

            return response()->json([
                'success' => true,
                'message' => 'Test di performance completato',
                'iterations' => $iterations,
                'times' => $times,
                'avg_write_time' => $times['write'] / $iterations,
                'avg_read_time' => $times['read'] / $iterations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel test di performance: ' . $e->getMessage()
            ], 500);
        }
    }
}
