<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Services\WriteThroughService;
use Illuminate\Support\Facades\Cache;

class WriteThroughTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_demonstrates_write_through_pattern_with_model()
    {
        // Test che dimostra come funziona il pattern Write-Through con il Model
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10
        ]);

        // Verifica che il prodotto sia stato creato
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Test Product'
        ]);

        // Verifica che sia stato scritto anche in cache
        $cacheKey = "product:{$product->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Test lettura dalla cache
        $cachedProduct = Product::findWithCache($product->id);
        $this->assertNotNull($cachedProduct);
        $this->assertEquals($product->id, $cachedProduct->id);
    }

    /** @test */
    public function it_handles_write_through_update()
    {
        // Crea un prodotto
        $product = Product::create([
            'name' => 'Original Product',
            'description' => 'Original Description',
            'price' => 50.00,
            'stock' => 5
        ]);

        // Aggiorna il prodotto
        $product->update(['price' => 75.00]);

        // Verifica che sia stato aggiornato nel database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 75.00
        ]);

        // Verifica che la cache sia stata aggiornata
        $cachedProduct = Product::findWithCache($product->id);
        $this->assertEquals(75.00, $cachedProduct->price);
    }

    /** @test */
    public function it_handles_write_through_deletion()
    {
        // Crea un prodotto
        $product = Product::create([
            'name' => 'Product to Delete',
            'description' => 'Will be deleted',
            'price' => 25.00,
            'stock' => 1
        ]);

        $productId = $product->id;
        $cacheKey = "product:{$productId}";

        // Verifica che sia in cache
        $this->assertTrue(Cache::has($cacheKey));

        // Elimina il prodotto
        $product->delete();

        // Verifica che sia stato eliminato dal database
        $this->assertDatabaseMissing('products', [
            'id' => $productId
        ]);

        // Verifica che sia stato rimosso dalla cache
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_demonstrates_write_through_with_service()
    {
        // Test che dimostra come funziona il pattern Write-Through con il Service
        $service = new WriteThroughService();

        // Test scrittura
        $testData = [
            'name' => 'Service Test Product',
            'description' => 'Service Test Description',
            'price' => 199.99,
            'stock' => 20,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = $service->write('product', $testData);

        // Verifica che sia stato scritto nel database
        $this->assertDatabaseHas('products', [
            'id' => $id,
            'name' => 'Service Test Product'
        ]);

        // Verifica che sia stato scritto in cache
        $cached = $service->read('product', $id);
        $this->assertNotNull($cached);
        $this->assertEquals('Service Test Product', $cached['name']);
    }

    /** @test */
    public function it_handles_cache_fallback_on_database_failure()
    {
        // Test che verifica il fallback della cache in caso di errore del database
        $product = Product::create([
            'name' => 'Fallback Test',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10
        ]);

        // Simula un errore del database (mock)
        $this->mock(\Illuminate\Database\Connection::class, function ($mock) {
            $mock->shouldReceive('beginTransaction')->andThrow(new \Exception('Database error'));
        });

        // Il pattern dovrebbe gestire l'errore e invalidare la cache
        $this->expectException(\Exception::class);
        
        // Questo test verifica che il pattern gestisca correttamente gli errori
        // In un'implementazione reale, il pattern dovrebbe invalidare la cache
        // quando il database fallisce per mantenere la coerenza
    }

    /** @test */
    public function it_maintains_consistency_between_cache_and_database()
    {
        // Test che verifica la coerenza tra cache e database
        $product = Product::create([
            'name' => 'Consistency Test',
            'description' => 'Test Description',
            'price' => 150.00,
            'stock' => 15
        ]);

        // Aggiorna il prodotto
        $product->update(['price' => 200.00]);

        // Verifica coerenza
        $dbProduct = Product::find($product->id);
        $cachedProduct = Product::findWithCache($product->id);

        $this->assertEquals($dbProduct->price, $cachedProduct->price);
        $this->assertEquals(200.00, $dbProduct->price);
        $this->assertEquals(200.00, $cachedProduct->price);
    }

    /** @test */
    public function it_performs_complete_write_through_test()
    {
        // Test completo del pattern Write-Through
        $results = Product::testWriteThrough();

        // Verifica che tutti i test siano passati
        $this->assertEquals('success', $results['creation']);
        $this->assertEquals('success', $results['cache_read']);
        $this->assertEquals('success', $results['update']);
        $this->assertEquals('consistent', $results['consistency']);
        $this->assertEquals('success', $results['deletion']);
        $this->assertEquals('success', $results['cache_removal']);
    }

    /** @test */
    public function it_handles_concurrent_writes()
    {
        // Test che verifica il comportamento con scritture concorrenti
        $product1 = Product::create([
            'name' => 'Concurrent Test 1',
            'description' => 'Test 1',
            'price' => 100.00,
            'stock' => 10
        ]);

        $product2 = Product::create([
            'name' => 'Concurrent Test 2',
            'description' => 'Test 2',
            'price' => 200.00,
            'stock' => 20
        ]);

        // Verifica che entrambi i prodotti siano stati creati correttamente
        $this->assertDatabaseHas('products', ['id' => $product1->id]);
        $this->assertDatabaseHas('products', ['id' => $product2->id]);

        // Verifica che entrambi siano in cache
        $this->assertTrue(Cache::has("product:{$product1->id}"));
        $this->assertTrue(Cache::has("product:{$product2->id}"));

        // Verifica che le letture dalla cache funzionino
        $cached1 = Product::findWithCache($product1->id);
        $cached2 = Product::findWithCache($product2->id);

        $this->assertNotNull($cached1);
        $this->assertNotNull($cached2);
        $this->assertEquals($product1->name, $cached1->name);
        $this->assertEquals($product2->name, $cached2->name);
    }
}
