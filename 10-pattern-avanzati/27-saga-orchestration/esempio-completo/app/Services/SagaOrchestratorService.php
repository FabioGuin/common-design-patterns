<?php

namespace App\Services;

use App\Models\Saga;
use App\Models\SagaStep;
use App\Jobs\ProcessSagaStepJob;
use App\Jobs\CompensateSagaStepJob;
use App\Jobs\CompleteSagaJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Exception;

/**
 * Servizio per l'orchestrazione delle Saga
 * 
 * Questo servizio coordina i passaggi di una saga, gestisce la compensazione
 * in caso di errori e mantiene lo stato della saga.
 */
class SagaOrchestratorService
{
    private string $id;
    private array $sagaDefinitions;
    private array $compensationStrategies;
    private int $maxRetries;
    private int $timeoutSeconds;
    private int $compensationTimeoutSeconds;

    public function __construct()
    {
        $this->id = 'saga-orchestrator-' . uniqid();
        $this->maxRetries = 3;
        $this->timeoutSeconds = 300; // 5 minuti
        $this->compensationTimeoutSeconds = 600; // 10 minuti
        
        $this->sagaDefinitions = [
            'create_order' => [
                'steps' => [
                    'validate_user',
                    'reserve_inventory',
                    'create_order',
                    'process_payment',
                    'send_notification'
                ],
                'compensation_order' => [
                    'send_notification',
                    'process_payment',
                    'create_order',
                    'reserve_inventory',
                    'validate_user'
                ]
            ],
            'cancel_order' => [
                'steps' => [
                    'validate_order',
                    'refund_payment',
                    'release_inventory',
                    'cancel_order',
                    'send_cancellation_notification'
                ],
                'compensation_order' => [
                    'send_cancellation_notification',
                    'cancel_order',
                    'release_inventory',
                    'refund_payment',
                    'validate_order'
                ]
            ]
        ];
        
        $this->compensationStrategies = [
            'validate_user' => 'unvalidate_user',
            'reserve_inventory' => 'release_inventory',
            'create_order' => 'delete_order',
            'process_payment' => 'refund_payment',
            'send_notification' => 'cancel_notification',
            'validate_order' => 'unvalidate_order',
            'refund_payment' => 'recharge_payment',
            'release_inventory' => 'reserve_inventory',
            'cancel_order' => 'restore_order',
            'send_cancellation_notification' => 'cancel_cancellation_notification'
        ];
        
        Log::info('SagaOrchestratorService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Avvia una nuova saga
     */
    public function startSaga(string $sagaType, array $data): array
    {
        if (!isset($this->sagaDefinitions[$sagaType])) {
            throw new Exception("Saga type '{$sagaType}' not defined");
        }

        $saga = new Saga([
            'type' => $sagaType,
            'status' => 'started',
            'data' => $data,
            'current_step' => 0,
            'total_steps' => count($this->sagaDefinitions[$sagaType]['steps']),
            'started_at' => now(),
            'timeout_at' => now()->addSeconds($this->timeoutSeconds)
        ]);
        $saga->save();

        Log::info('Saga started', [
            'saga_id' => $saga->id,
            'type' => $sagaType,
            'orchestrator' => $this->id
        ]);

        // Avvia il primo passaggio
        $this->executeNextStep($saga);

        return [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'status' => $saga->status,
            'current_step' => $saga->current_step,
            'total_steps' => $saga->total_steps,
            'started_at' => $saga->started_at,
            'timeout_at' => $saga->timeout_at
        ];
    }

    /**
     * Esegue il prossimo passaggio della saga
     */
    public function executeNextStep(Saga $saga): void
    {
        $sagaDefinition = $this->sagaDefinitions[$saga->type];
        $steps = $sagaDefinition['steps'];

        if ($saga->current_step >= count($steps)) {
            $this->completeSaga($saga);
            return;
        }

        $stepName = $steps[$saga->current_step];
        
        // Crea il record del passaggio
        $sagaStep = new SagaStep([
            'saga_id' => $saga->id,
            'step_name' => $stepName,
            'status' => 'pending',
            'started_at' => now(),
            'timeout_at' => now()->addSeconds($this->timeoutSeconds)
        ]);
        $sagaStep->save();

        // Aggiorna la saga
        $saga->current_step++;
        $saga->save();

        Log::info('Executing saga step', [
            'saga_id' => $saga->id,
            'step_name' => $stepName,
            'step_number' => $saga->current_step,
            'orchestrator' => $this->id
        ]);

        // Avvia il job per eseguire il passaggio
        Queue::push(new ProcessSagaStepJob($saga->id, $sagaStep->id, $stepName, $saga->data));
    }

    /**
     * Completa un passaggio della saga
     */
    public function completeStep(int $sagaId, int $stepId, array $result): void
    {
        $saga = Saga::find($sagaId);
        if (!$saga) {
            throw new Exception("Saga {$sagaId} not found");
        }

        $sagaStep = SagaStep::find($stepId);
        if (!$sagaStep) {
            throw new Exception("Saga step {$stepId} not found");
        }

        $sagaStep->status = 'completed';
        $sagaStep->result = $result;
        $sagaStep->completed_at = now();
        $sagaStep->save();

        Log::info('Saga step completed', [
            'saga_id' => $sagaId,
            'step_id' => $stepId,
            'step_name' => $sagaStep->step_name,
            'orchestrator' => $this->id
        ]);

        // Esegue il prossimo passaggio
        $this->executeNextStep($saga);
    }

    /**
     * Fallisce un passaggio della saga
     */
    public function failStep(int $sagaId, int $stepId, string $error): void
    {
        $saga = Saga::find($sagaId);
        if (!$saga) {
            throw new Exception("Saga {$sagaId} not found");
        }

        $sagaStep = SagaStep::find($stepId);
        if (!$sagaStep) {
            throw new Exception("Saga step {$stepId} not found");
        }

        $sagaStep->status = 'failed';
        $sagaStep->error = $error;
        $sagaStep->failed_at = now();
        $sagaStep->save();

        Log::error('Saga step failed', [
            'saga_id' => $sagaId,
            'step_id' => $stepId,
            'step_name' => $sagaStep->step_name,
            'error' => $error,
            'orchestrator' => $this->id
        ]);

        // Avvia la compensazione
        $this->startCompensation($saga);
    }

    /**
     * Avvia la compensazione della saga
     */
    public function startCompensation(Saga $saga): void
    {
        $saga->status = 'compensating';
        $saga->save();

        Log::info('Starting saga compensation', [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'orchestrator' => $this->id
        ]);

        // Ottiene i passaggi completati in ordine inverso
        $completedSteps = SagaStep::where('saga_id', $saga->id)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($completedSteps as $step) {
            $compensationStep = $this->compensationStrategies[$step->step_name] ?? null;
            if ($compensationStep) {
                Queue::push(new CompensateSagaStepJob(
                    $saga->id,
                    $step->id,
                    $compensationStep,
                    $step->result
                ));
            }
        }

        // Completa la compensazione
        Queue::push(new CompleteSagaJob($saga->id, 'compensated'));
    }

    /**
     * Completa la saga
     */
    public function completeSaga(Saga $saga): void
    {
        $saga->status = 'completed';
        $saga->completed_at = now();
        $saga->save();

        Log::info('Saga completed', [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'orchestrator' => $this->id
        ]);

        // Avvia il job di completamento
        Queue::push(new CompleteSagaJob($saga->id, 'completed'));
    }

    /**
     * Ottiene lo stato di una saga
     */
    public function getSagaStatus(int $sagaId): array
    {
        $saga = Saga::find($sagaId);
        if (!$saga) {
            throw new Exception("Saga {$sagaId} not found");
        }

        $steps = SagaStep::where('saga_id', $sagaId)
            ->orderBy('id')
            ->get();

        return [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'status' => $saga->status,
            'current_step' => $saga->current_step,
            'total_steps' => $saga->total_steps,
            'progress' => $saga->total_steps > 0 ? round(($saga->current_step / $saga->total_steps) * 100, 2) : 0,
            'started_at' => $saga->started_at,
            'completed_at' => $saga->completed_at,
            'timeout_at' => $saga->timeout_at,
            'steps' => $steps->map(function ($step) {
                return [
                    'id' => $step->id,
                    'step_name' => $step->step_name,
                    'status' => $step->status,
                    'started_at' => $step->started_at,
                    'completed_at' => $step->completed_at,
                    'failed_at' => $step->failed_at,
                    'error' => $step->error,
                    'result' => $step->result
                ];
            })->toArray()
        ];
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        $totalSagas = Saga::count();
        $completedSagas = Saga::where('status', 'completed')->count();
        $failedSagas = Saga::where('status', 'compensated')->count();
        $runningSagas = Saga::whereIn('status', ['started', 'compensating'])->count();

        return [
            'id' => $this->id,
            'service' => 'SagaOrchestratorService',
            'total_sagas' => $totalSagas,
            'completed_sagas' => $completedSagas,
            'failed_sagas' => $failedSagas,
            'running_sagas' => $runningSagas,
            'success_rate' => $totalSagas > 0 ? round(($completedSagas / $totalSagas) * 100, 2) : 100,
            'max_retries' => $this->maxRetries,
            'timeout_seconds' => $this->timeoutSeconds,
            'compensation_timeout_seconds' => $this->compensationTimeoutSeconds,
            'saga_types' => array_keys($this->sagaDefinitions)
        ];
    }

    /**
     * Ottiene la cronologia delle saga
     */
    public function getSagaHistory(int $limit = 50): array
    {
        $sagas = Saga::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $sagas->map(function ($saga) {
            return [
                'id' => $saga->id,
                'type' => $saga->type,
                'status' => $saga->status,
                'current_step' => $saga->current_step,
                'total_steps' => $saga->total_steps,
                'started_at' => $saga->started_at,
                'completed_at' => $saga->completed_at,
                'created_at' => $saga->created_at
            ];
        })->toArray();
    }

    /**
     * Pulisce le saga vecchie
     */
    public function cleanupOldSagas(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = Saga::where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['completed', 'compensated'])
            ->delete();

        Log::info('Cleaned up old sagas', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate,
            'orchestrator' => $this->id
        ]);

        return $deletedCount;
    }
}
