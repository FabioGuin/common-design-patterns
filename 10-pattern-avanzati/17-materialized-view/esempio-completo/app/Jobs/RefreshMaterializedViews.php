<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MaterializedViewService;
use Illuminate\Support\Facades\Log;

class RefreshMaterializedViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $viewService;
    protected $maxTries = 3;
    protected $timeout = 300; // 5 minuti

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->viewService = new MaterializedViewService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Materialized View: Inizio aggiornamento viste materializzate");
            
            $results = $this->viewService->refreshAllViews();
            
            $successCount = count(array_filter($results, fn($result) => $result === 'refreshed'));
            $totalCount = count($results);
            
            Log::info("Materialized View: Aggiornamento completato", [
                'successful' => $successCount,
                'total' => $totalCount,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error("Materialized View: Errore nell'aggiornamento delle viste", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Rilancia l'eccezione per il retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Materialized View: Job fallito definitivamente", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'materialized-view',
            'refresh',
            'scheduled'
        ];
    }
}
