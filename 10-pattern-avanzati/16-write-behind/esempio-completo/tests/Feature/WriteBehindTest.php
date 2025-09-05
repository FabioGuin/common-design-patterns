<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\LogEntry;
use App\Services\WriteBehindService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class WriteBehindTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_demonstrates_write_behind_pattern_with_model()
    {
        // Test che dimostra come funziona il pattern Write-Behind con il Model
        $log = LogEntry::create([
            'level' => 'info',
            'message' => 'Test Write-Behind',
            'context' => ['test' => true],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);

        // Verifica che il log sia stato creato con ID temporaneo
        $this->assertNotNull($log->id);
        $this->assertStringStartsWith('log_', $log->id);

        // Verifica che sia stato scritto in cache
        $cacheKey = "log_entry:{$log->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Verifica che sia stato aggiunto alla coda
        $this->assertEquals(1, Queue::size());
    }

    /** @test */
    public function it_handles_immediate_cache_write()
    {
        // Test che verifica la scrittura immediata in cache
        $log = LogEntry::create([
            'level' => 'info',
            'message' => 'Cache Test',
            'context' => ['cache_test' => true],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);

        // Verifica che sia in cache immediatamente
        $cacheKey = "log_entry:{$log->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Verifica che i dati in cache siano corretti
        $cached = Cache::get($cacheKey);
        $this->assertEquals('Cache Test', $cached['message']);
        $this->assertEquals('info', $cached['level']);
    }

    /** @test */
    public function it_handles_queue_processing()
    {
        // Test che verifica l'aggiunta alla coda
        Queue::fake();

        $log = LogEntry::create([
            'level' => 'info',
            'message' => 'Queue Test',
            'context' => ['queue_test' => true],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);

        // Verifica che sia stato aggiunto alla coda
        Queue::assertPushed(\App\Jobs\ProcessLogBatch::class);
    }

    /** @test */
    public function it_demonstrates_write_behind_with_service()
    {
        // Test che dimostra come funziona il pattern Write-Behind con il Service
        $service = new WriteBehindService();

        // Test scrittura
        $testData = [
            'level' => 'info',
            'message' => 'Service Test Write-Behind',
            'context' => ['service_test' => true],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ];

        $id = $service->write('log', $testData);

        // Verifica che sia stato scritto in cache
        $cached = $service->read('log', $id);
        $this->assertNotNull($cached);
        $this->assertEquals('Service Test Write-Behind', $cached['message']);
    }

    /** @test */
    public function it_handles_batch_processing()
    {
        // Test che verifica il batch processing
        $service = new WriteBehindService();

        $batchData = [];
        for ($i = 0; $i < 10; $i++) {
            $batchData[] = [
                'level' => 'info',
                'message' => "Batch test {$i}",
                'context' => ['batch_test' => true, 'iteration' => $i],
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent'
            ];
        }

        $results = $service->writeBatch('log', $batchData);

        // Verifica che tutti i batch siano stati processati
        $this->assertCount(10, $results);
        $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
        $this->assertEquals(10, $successCount);
    }

    /** @test */
    public function it_handles_cache_read_with_fallback()
    {
        // Test che verifica la lettura dalla cache con fallback al database
        $log = LogEntry::create([
            'level' => 'info',
            'message' => 'Fallback Test',
            'context' => ['fallback_test' => true],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);

        // Simula la scrittura nel database (in un test reale, questo sarebbe fatto dal job)
        $log->fill(['id' => $log->id])->saveQuietly();

        // Pulisce la cache per simulare il fallback
        $log->clearCache();

        // Verifica che la lettura funzioni con fallback
        $cached = LogEntry::findWithCache($log->id);
        $this->assertNotNull($cached);
        $this->assertEquals('Fallback Test', $cached->message);
    }

    /** @test */
    public function it_performs_complete_write_behind_test()
    {
        // Test completo del pattern Write-Behind
        $results = LogEntry::testWriteBehind();

        // Verifica che tutti i test siano passati
        $this->assertEquals('success', $results['creation']);
        $this->assertEquals('success', $results['cache_read']);
        $this->assertEquals('success', $results['queued']);
        $this->assertArrayHasKey('performance', $results);
        $this->assertEquals('yes', $results['cache_exists']);
    }

    /** @test */
    public function it_handles_high_frequency_writes()
    {
        // Test che verifica il comportamento con scritture ad alta frequenza
        $start = microtime(true);
        
        $logs = [];
        for ($i = 0; $i < 100; $i++) {
            $log = LogEntry::create([
                'level' => 'info',
                'message' => "High frequency test {$i}",
                'context' => ['high_frequency' => true, 'iteration' => $i],
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent'
            ]);
            $logs[] = $log;
        }

        $totalTime = microtime(true) - $start;

        // Verifica che le scritture siano state veloci (meno di 1 secondo per 100 log)
        $this->assertLessThan(1.0, $totalTime);

        // Verifica che tutti i log siano in cache
        foreach ($logs as $log) {
            $cacheKey = "log_entry:{$log->id}";
            $this->assertTrue(Cache::has($cacheKey));
        }
    }

    /** @test */
    public function it_handles_concurrent_writes()
    {
        // Test che verifica il comportamento con scritture concorrenti
        $logs = [];
        
        // Simula scritture concorrenti
        for ($i = 0; $i < 50; $i++) {
            $log = LogEntry::create([
                'level' => 'info',
                'message' => "Concurrent test {$i}",
                'context' => ['concurrent_test' => true, 'iteration' => $i],
                'user_id' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent'
            ]);
            $logs[] = $log;
        }

        // Verifica che tutti i log siano stati creati
        $this->assertCount(50, $logs);

        // Verifica che tutti siano in cache
        foreach ($logs as $log) {
            $cacheKey = "log_entry:{$log->id}";
            $this->assertTrue(Cache::has($cacheKey));
        }

        // Verifica che tutti siano stati aggiunti alla coda
        $this->assertEquals(50, Queue::size());
    }
}
