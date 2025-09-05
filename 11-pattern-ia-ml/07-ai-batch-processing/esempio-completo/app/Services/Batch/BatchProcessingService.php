<?php

namespace App\Services\Batch;

use App\Models\BatchJob;
use App\Models\BatchRequest;
use App\Services\AI\AIGatewayService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class BatchProcessingService
{
    public function __construct(
        private AIGatewayService $aiGateway
    ) {}

    /**
     * Crea un nuovo batch job con le richieste specificate
     */
    public function createBatch(array $requests, string $provider, string $model, array $options = []): BatchJob
    {
        $batchJob = BatchJob::create([
            'name' => $options['name'] ?? 'Batch ' . now()->format('Y-m-d H:i:s'),
            'status' => BatchJob::STATUS_PENDING,
            'total_requests' => count($requests),
            'provider' => $provider,
            'model' => $model,
            'batch_size' => $options['batch_size'] ?? config('ai.batch.default_size', 100),
            'priority' => $options['priority'] ?? BatchJob::PRIORITY_NORMAL,
            'scheduled_at' => $options['scheduled_at'] ?? now(),
            'metadata' => $options['metadata'] ?? [],
        ]);

        foreach ($requests as $request) {
            $batchJob->requests()->create([
                'input' => $request['input'],
                'expected_output' => $request['expected_output'] ?? null,
                'status' => BatchRequest::STATUS_PENDING,
                'priority' => $request['priority'] ?? BatchRequest::PRIORITY_NORMAL,
                'metadata' => $request['metadata'] ?? [],
            ]);
        }

        Log::info('Batch job created', [
            'batch_id' => $batchJob->id,
            'total_requests' => $batchJob->total_requests,
            'provider' => $provider,
            'model' => $model,
        ]);

        return $batchJob;
    }

    /**
     * Processa un batch job
     */
    public function processBatch(BatchJob $batchJob): void
    {
        if (!$batchJob->canBeProcessed()) {
            Log::warning('Batch job cannot be processed', [
                'batch_id' => $batchJob->id,
                'status' => $batchJob->status,
            ]);
            return;
        }

        $batchJob->markAsProcessing();

        try {
            $requests = $this->getPendingRequests($batchJob);
            
            if ($requests->isEmpty()) {
                $this->completeBatch($batchJob);
                return;
            }

            Log::info('Processing batch requests', [
                'batch_id' => $batchJob->id,
                'request_count' => $requests->count(),
            ]);

            $results = $this->processBatchRequests($requests, $batchJob);
            $this->updateBatchResults($batchJob, $requests, $results);

            if ($this->isBatchComplete($batchJob)) {
                $this->completeBatch($batchJob);
            }

        } catch (Exception $e) {
            $this->handleBatchError($batchJob, $e);
        }
    }

    /**
     * Ottiene le richieste in attesa per un batch
     */
    private function getPendingRequests(BatchJob $batchJob): Collection
    {
        return $batchJob->requests()
            ->where('status', BatchRequest::STATUS_PENDING)
            ->limit($batchJob->batch_size)
            ->get();
    }

    /**
     * Processa le richieste del batch
     */
    private function processBatchRequests(Collection $requests, BatchJob $batchJob): array
    {
        $batchInputs = $requests->pluck('input')->toArray();
        
        $startTime = microtime(true);
        
        $responses = $this->aiGateway->processBatch([
            'provider' => $batchJob->provider,
            'model' => $batchJob->model,
            'requests' => $batchInputs,
        ]);

        $processingTime = (microtime(true) - $startTime) * 1000; // in millisecondi

        Log::info('Batch processing completed', [
            'batch_id' => $batchJob->id,
            'processing_time_ms' => $processingTime,
            'request_count' => count($responses),
        ]);

        return $responses;
    }

    /**
     * Aggiorna i risultati del batch
     */
    private function updateBatchResults(BatchJob $batchJob, Collection $requests, array $results): void
    {
        foreach ($requests as $index => $request) {
            $request->markAsProcessing();

            if (isset($results[$index])) {
                $result = $results[$index];
                
                if ($result['success']) {
                    $request->markAsCompleted(
                        $result['output'],
                        $result['processing_time_ms'] ?? null
                    );
                } else {
                    $request->markAsFailed($result['error'] ?? 'Unknown error');
                }
            } else {
                $request->markAsFailed('No response received');
            }
        }

        $batchJob->updateStatistics();
    }

    /**
     * Verifica se il batch è completo
     */
    private function isBatchComplete(BatchJob $batchJob): bool
    {
        return $batchJob->isReadyForCompletion();
    }

    /**
     * Completa il batch
     */
    private function completeBatch(BatchJob $batchJob): void
    {
        $batchJob->markAsCompleted();

        Log::info('Batch job completed', [
            'batch_id' => $batchJob->id,
            'processed_requests' => $batchJob->processed_requests,
            'failed_requests' => $batchJob->failed_requests,
            'success_rate' => $batchJob->getSuccessRate(),
        ]);
    }

    /**
     * Gestisce gli errori del batch
     */
    private function handleBatchError(BatchJob $batchJob, Exception $e): void
    {
        $batchJob->markAsFailed($e->getMessage());

        Log::error('Batch processing failed', [
            'batch_id' => $batchJob->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Crea un batch ricorrente
     */
    public function createRecurringBatch(string $name, array $config): void
    {
        // Implementazione per batch ricorrenti
        // Questo potrebbe essere gestito da un scheduler esterno
        Log::info('Creating recurring batch', [
            'name' => $name,
            'config' => $config,
        ]);
    }

    /**
     * Ottiene statistiche sui batch
     */
    public function getBatchStatistics(array $filters = []): array
    {
        $query = BatchJob::query();

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $batches = $query->get();

        return [
            'total_batches' => $batches->count(),
            'completed_batches' => $batches->where('status', BatchJob::STATUS_COMPLETED)->count(),
            'failed_batches' => $batches->where('status', BatchJob::STATUS_FAILED)->count(),
            'processing_batches' => $batches->where('status', BatchJob::STATUS_PROCESSING)->count(),
            'total_requests' => $batches->sum('total_requests'),
            'processed_requests' => $batches->sum('processed_requests'),
            'failed_requests' => $batches->sum('failed_requests'),
            'average_success_rate' => $batches->avg('success_rate') ?? 0,
            'average_processing_time' => $batches->avg('processing_time_seconds') ?? 0,
        ];
    }

    /**
     * Cancella un batch
     */
    public function cancelBatch(BatchJob $batchJob): void
    {
        if ($batchJob->isCompleted() || $batchJob->isFailed()) {
            throw new Exception('Cannot cancel completed or failed batch');
        }

        $batchJob->cancel();

        Log::info('Batch job cancelled', [
            'batch_id' => $batchJob->id,
        ]);
    }

    /**
     * Riprova un batch fallito
     */
    public function retryBatch(BatchJob $batchJob): void
    {
        if (!$batchJob->isFailed()) {
            throw new Exception('Can only retry failed batches');
        }

        // Reset delle richieste fallite
        $batchJob->requests()
            ->where('status', BatchRequest::STATUS_FAILED)
            ->update(['status' => BatchRequest::STATUS_PENDING]);

        $batchJob->update([
            'status' => BatchJob::STATUS_PENDING,
            'error_message' => null,
            'failed_requests' => 0,
        ]);

        Log::info('Batch job retried', [
            'batch_id' => $batchJob->id,
        ]);
    }
}
