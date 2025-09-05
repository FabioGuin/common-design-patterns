<?php

namespace App\Jobs;

use App\Models\BatchJob;
use App\Services\Batch\BatchProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minuti
    public int $backoff = 30; // 30 secondi tra i retry

    public function __construct(
        private BatchJob $batchJob
    ) {
        $this->onQueue('ai-batch');
    }

    /**
     * Esegue il job
     */
    public function handle(BatchProcessingService $batchService): void
    {
        Log::info('Starting batch processing job', [
            'batch_id' => $this->batchJob->id,
            'job_id' => $this->job->getJobId(),
        ]);

        try {
            $batchService->processBatch($this->batchJob);

            Log::info('Batch processing job completed successfully', [
                'batch_id' => $this->batchJob->id,
                'job_id' => $this->job->getJobId(),
            ]);

        } catch (Throwable $e) {
            Log::error('Batch processing job failed', [
                'batch_id' => $this->batchJob->id,
                'job_id' => $this->job->getJobId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Batch processing job permanently failed', [
            'batch_id' => $this->batchJob->id,
            'job_id' => $this->job->getJobId(),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Marca il batch come fallito
        $this->batchJob->update([
            'status' => BatchJob::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
            'metadata' => array_merge(
                $this->batchJob->metadata ?? [],
                [
                    'job_failed' => true,
                    'job_error' => $exception->getMessage(),
                    'failed_at' => now()->toISOString(),
                    'attempts' => $this->attempts(),
                ]
            )
        ]);
    }

    /**
     * Determina se il job deve essere ritentato
     */
    public function shouldRetry(Throwable $exception): bool
    {
        // Non ritentare per errori di validazione o configurazione
        if ($exception instanceof \InvalidArgumentException) {
            return false;
        }

        // Ritenta per errori di rete o temporanei
        if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
            return true;
        }

        // Ritenta per errori di rate limiting
        if (str_contains($exception->getMessage(), 'rate limit')) {
            return true;
        }

        // Ritenta per errori di timeout
        if (str_contains($exception->getMessage(), 'timeout')) {
            return true;
        }

        return true;
    }

    /**
     * Calcola il tempo di attesa per il prossimo retry
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // 30s, 1m, 2m
    }

    /**
     * Ottiene i tag per il job
     */
    public function tags(): array
    {
        return [
            'batch:' . $this->batchJob->id,
            'provider:' . $this->batchJob->provider,
            'model:' . $this->batchJob->model,
        ];
    }
}
