<?php

namespace Tests\Feature;

use App\Models\BatchJob;
use App\Models\BatchRequest;
use App\Services\Batch\BatchProcessingService;
use App\Services\AI\AIGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Mockery;

class AIBatchProcessingTest extends TestCase
{
    use RefreshDatabase;

    private BatchProcessingService $batchService;
    private AIGatewayService $aiGateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->aiGateway = Mockery::mock(AIGatewayService::class);
        $this->batchService = new BatchProcessingService($this->aiGateway);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_a_batch_job()
    {
        $requests = [
            ['input' => 'Test input 1', 'priority' => 'normal'],
            ['input' => 'Test input 2', 'priority' => 'high'],
        ];

        $batchJob = $this->batchService->createBatch(
            requests: $requests,
            provider: 'openai',
            model: 'gpt-3.5-turbo'
        );

        $this->assertInstanceOf(BatchJob::class, $batchJob);
        $this->assertEquals('openai', $batchJob->provider);
        $this->assertEquals('gpt-3.5-turbo', $batchJob->model);
        $this->assertEquals(2, $batchJob->total_requests);
        $this->assertEquals(BatchJob::STATUS_PENDING, $batchJob->status);
        $this->assertCount(2, $batchJob->requests);
    }

    /** @test */
    public function it_can_process_a_batch_job()
    {
        // Crea un batch job
        $batchJob = BatchJob::factory()->create([
            'status' => BatchJob::STATUS_PENDING,
            'total_requests' => 2,
        ]);

        // Crea le richieste
        BatchRequest::factory()->create([
            'batch_job_id' => $batchJob->id,
            'status' => BatchRequest::STATUS_PENDING,
            'input' => 'Test input 1',
        ]);

        BatchRequest::factory()->create([
            'batch_job_id' => $batchJob->id,
            'status' => BatchRequest::STATUS_PENDING,
            'input' => 'Test input 2',
        ]);

        // Mock della risposta AI
        $this->aiGateway->shouldReceive('processBatch')
            ->once()
            ->andReturn([
                [
                    'success' => true,
                    'output' => 'Test output 1',
                    'processing_time_ms' => 1000,
                ],
                [
                    'success' => true,
                    'output' => 'Test output 2',
                    'processing_time_ms' => 1200,
                ],
            ]);

        // Processa il batch
        $this->batchService->processBatch($batchJob);

        // Verifica che il batch sia completato
        $batchJob->refresh();
        $this->assertEquals(BatchJob::STATUS_COMPLETED, $batchJob->status);
        $this->assertEquals(2, $batchJob->processed_requests);
        $this->assertEquals(0, $batchJob->failed_requests);

        // Verifica che le richieste siano state processate
        $requests = $batchJob->requests;
        $this->assertCount(2, $requests);
        $this->assertTrue($requests->every(fn($request) => $request->status === BatchRequest::STATUS_COMPLETED));
    }

    /** @test */
    public function it_handles_batch_processing_errors()
    {
        // Crea un batch job
        $batchJob = BatchJob::factory()->create([
            'status' => BatchJob::STATUS_PENDING,
            'total_requests' => 1,
        ]);

        // Crea una richiesta
        BatchRequest::factory()->create([
            'batch_job_id' => $batchJob->id,
            'status' => BatchRequest::STATUS_PENDING,
            'input' => 'Test input',
        ]);

        // Mock di un errore AI
        $this->aiGateway->shouldReceive('processBatch')
            ->once()
            ->andThrow(new \Exception('AI service unavailable'));

        // Processa il batch
        $this->batchService->processBatch($batchJob);

        // Verifica che il batch sia fallito
        $batchJob->refresh();
        $this->assertEquals(BatchJob::STATUS_FAILED, $batchJob->status);
        $this->assertNotNull($batchJob->error_message);
    }

    /** @test */
    public function it_can_cancel_a_batch_job()
    {
        $batchJob = BatchJob::factory()->create([
            'status' => BatchJob::STATUS_PENDING,
        ]);

        $this->batchService->cancelBatch($batchJob);

        $batchJob->refresh();
        $this->assertEquals(BatchJob::STATUS_CANCELLED, $batchJob->status);
    }

    /** @test */
    public function it_can_retry_a_failed_batch_job()
    {
        $batchJob = BatchJob::factory()->create([
            'status' => BatchJob::STATUS_FAILED,
            'failed_requests' => 2,
        ]);

        // Crea richieste fallite
        BatchRequest::factory()->create([
            'batch_job_id' => $batchJob->id,
            'status' => BatchRequest::STATUS_FAILED,
        ]);

        $this->batchService->retryBatch($batchJob);

        $batchJob->refresh();
        $this->assertEquals(BatchJob::STATUS_PENDING, $batchJob->status);
        $this->assertEquals(0, $batchJob->failed_requests);
    }

    /** @test */
    public function it_calculates_batch_statistics_correctly()
    {
        // Crea batch con stati diversi
        BatchJob::factory()->create(['status' => BatchJob::STATUS_COMPLETED, 'processed_requests' => 10]);
        BatchJob::factory()->create(['status' => BatchJob::STATUS_FAILED, 'failed_requests' => 5]);
        BatchJob::factory()->create(['status' => BatchJob::STATUS_PROCESSING, 'processed_requests' => 3]);

        $statistics = $this->batchService->getBatchStatistics();

        $this->assertEquals(3, $statistics['total_batches']);
        $this->assertEquals(1, $statistics['completed_batches']);
        $this->assertEquals(1, $statistics['failed_batches']);
        $this->assertEquals(1, $statistics['processing_batches']);
        $this->assertEquals(13, $statistics['processed_requests']);
        $this->assertEquals(5, $statistics['failed_requests']);
    }

    /** @test */
    public function it_can_create_batch_via_api()
    {
        $requests = [
            ['input' => 'Test input 1'],
            ['input' => 'Test input 2'],
        ];

        $response = $this->postJson('/api/batch/create', [
            'requests' => $requests,
            'provider' => 'openai',
            'model' => 'gpt-3.5-turbo',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'batch_id',
                    'status',
                    'total_requests',
                    'provider',
                    'model',
                ]
            ]);

        $this->assertDatabaseHas('batch_jobs', [
            'provider' => 'openai',
            'model' => 'gpt-3.5-turbo',
            'total_requests' => 2,
        ]);
    }

    /** @test */
    public function it_can_get_batch_status_via_api()
    {
        $batchJob = BatchJob::factory()->create([
            'status' => BatchJob::STATUS_PROCESSING,
            'processed_requests' => 5,
            'total_requests' => 10,
        ]);

        $response = $this->getJson("/api/batch/{$batchJob->id}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'status',
                    'progress_percentage',
                    'processed_requests',
                    'total_requests',
                    'failed_requests',
                ]
            ]);

        $response->assertJson([
            'data' => [
                'id' => $batchJob->id,
                'status' => BatchJob::STATUS_PROCESSING,
                'progress_percentage' => 50.0,
            ]
        ]);
    }

    /** @test */
    public function it_can_create_sample_batch_via_api()
    {
        $response = $this->postJson('/api/batch/sample');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'batch_id',
                    'status',
                    'total_requests',
                ]
            ]);

        $this->assertDatabaseHas('batch_jobs', [
            'provider' => 'openai',
            'model' => 'gpt-3.5-turbo',
            'total_requests' => 3,
        ]);
    }

    /** @test */
    public function it_validates_batch_creation_input()
    {
        $response = $this->postJson('/api/batch/create', [
            'requests' => [],
            'provider' => 'invalid_provider',
            'model' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests', 'provider', 'model']);
    }

    /** @test */
    public function it_can_get_batch_list_via_api()
    {
        BatchJob::factory()->count(5)->create();

        $response = $this->getJson('/api/batch/');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    /** @test */
    public function it_can_get_batch_statistics_via_api()
    {
        BatchJob::factory()->create(['status' => BatchJob::STATUS_COMPLETED]);
        BatchJob::factory()->create(['status' => BatchJob::STATUS_FAILED]);

        $response = $this->getJson('/api/batch/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_batches',
                    'completed_batches',
                    'failed_batches',
                    'processing_batches',
                    'total_requests',
                    'processed_requests',
                    'failed_requests',
                ]
            ]);
    }
}
