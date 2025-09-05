<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLogBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logData;
    protected $operation;
    protected $maxTries = 3;
    protected $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(array $logData, string $operation = 'create')
    {
        $this->logData = $logData;
        $this->operation = $operation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            switch ($this->operation) {
                case 'create':
                    $this->createLog();
                    break;
                case 'update':
                    $this->updateLog();
                    break;
                case 'delete':
                    $this->deleteLog();
                    break;
                default:
                    throw new \InvalidArgumentException("Operazione non supportata: {$this->operation}");
            }
            
            Log::info("Write-Behind: Job processato con successo", [
                'operation' => $this->operation,
                'log_id' => $this->logData['id'] ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Write-Behind: Errore nel processing del job", [
                'operation' => $this->operation,
                'error' => $e->getMessage(),
                'log_data' => $this->logData
            ]);
            
            // Rilancia l'eccezione per il retry
            throw $e;
        }
    }

    /**
     * Crea un nuovo log nel database
     */
    private function createLog()
    {
        DB::table('log_entries')->insert([
            'id' => $this->logData['id'],
            'level' => $this->logData['level'],
            'message' => $this->logData['message'],
            'context' => json_encode($this->logData['context'] ?? []),
            'user_id' => $this->logData['user_id'] ?? null,
            'ip_address' => $this->logData['ip_address'] ?? null,
            'user_agent' => $this->logData['user_agent'] ?? null,
            'created_at' => $this->logData['created_at'] ?? now(),
            'updated_at' => $this->logData['updated_at'] ?? now()
        ]);
    }

    /**
     * Aggiorna un log esistente nel database
     */
    private function updateLog()
    {
        $updateData = array_filter([
            'level' => $this->logData['level'] ?? null,
            'message' => $this->logData['message'] ?? null,
            'context' => isset($this->logData['context']) ? json_encode($this->logData['context']) : null,
            'user_id' => $this->logData['user_id'] ?? null,
            'ip_address' => $this->logData['ip_address'] ?? null,
            'user_agent' => $this->logData['user_agent'] ?? null,
            'updated_at' => $this->logData['updated_at'] ?? now()
        ], fn($value) => $value !== null);

        if (!empty($updateData)) {
            DB::table('log_entries')
                ->where('id', $this->logData['id'])
                ->update($updateData);
        }
    }

    /**
     * Elimina un log dal database
     */
    private function deleteLog()
    {
        DB::table('log_entries')
            ->where('id', $this->logData['id'])
            ->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Write-Behind: Job fallito definitivamente", [
            'operation' => $this->operation,
            'log_id' => $this->logData['id'] ?? 'unknown',
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
            'write-behind',
            'log-processing',
            'operation:' . $this->operation
        ];
    }
}
