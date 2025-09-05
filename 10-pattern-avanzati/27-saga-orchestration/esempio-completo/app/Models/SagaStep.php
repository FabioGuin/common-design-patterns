<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modello SagaStep per il Saga Orchestration Pattern
 * 
 * Questo modello rappresenta un singolo passaggio di una saga
 * e gestisce lo stato e i risultati dell'operazione.
 */
class SagaStep extends Model
{
    protected $fillable = [
        'saga_id',
        'step_name',
        'status',
        'started_at',
        'completed_at',
        'failed_at',
        'timeout_at',
        'result',
        'error',
        'retry_count',
        'max_retries'
    ];
    
    protected $casts = [
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'timeout_at' => 'datetime',
        'retry_count' => 'integer',
        'max_retries' => 'integer'
    ];
    
    /**
     * Relazione con la saga
     */
    public function saga(): BelongsTo
    {
        return $this->belongsTo(Saga::class);
    }
    
    /**
     * Verifica se il passaggio è completato
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    /**
     * Verifica se il passaggio è fallito
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
    
    /**
     * Verifica se il passaggio è in sospeso
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Verifica se il passaggio è in esecuzione
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }
    
    /**
     * Verifica se il passaggio è scaduto
     */
    public function isExpired(): bool
    {
        return $this->timeout_at && $this->timeout_at->isPast();
    }
    
    /**
     * Verifica se il passaggio può essere riprovato
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < $this->max_retries && 
               !$this->isExpired();
    }
    
    /**
     * Ottiene la durata del passaggio
     */
    public function getDuration(): ?int
    {
        if (!$this->started_at) {
            return null;
        }
        
        $endTime = $this->completed_at ?? $this->failed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }
    
    /**
     * Ottiene il tempo rimanente prima del timeout
     */
    public function getTimeRemaining(): ?int
    {
        if (!$this->timeout_at) {
            return null;
        }
        
        $remaining = now()->diffInSeconds($this->timeout_at, false);
        return $remaining > 0 ? $remaining : 0;
    }
    
    /**
     * Ottiene le statistiche del passaggio
     */
    public function getStats(): array
    {
        return [
            'step_id' => $this->id,
            'saga_id' => $this->saga_id,
            'step_name' => $this->step_name,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'failed_at' => $this->failed_at,
            'duration' => $this->getDuration(),
            'retry_count' => $this->retry_count,
            'max_retries' => $this->max_retries,
            'can_retry' => $this->canRetry(),
            'is_expired' => $this->isExpired(),
            'error' => $this->error,
            'result' => $this->result
        ];
    }
    
    /**
     * Marca il passaggio come in esecuzione
     */
    public function markAsRunning(): void
    {
        $this->status = 'running';
        $this->started_at = now();
        $this->timeout_at = now()->addMinutes(5);
        $this->save();
    }
    
    /**
     * Marca il passaggio come completato
     */
    public function markAsCompleted(array $result = []): void
    {
        $this->status = 'completed';
        $this->result = $result;
        $this->completed_at = now();
        $this->save();
    }
    
    /**
     * Marca il passaggio come fallito
     */
    public function markAsFailed(string $error, bool $incrementRetry = true): void
    {
        $this->status = 'failed';
        $this->error = $error;
        $this->failed_at = now();
        
        if ($incrementRetry) {
            $this->retry_count++;
        }
        
        $this->save();
    }
    
    /**
     * Riprova il passaggio
     */
    public function retry(): bool
    {
        if (!$this->canRetry()) {
            return false;
        }
        
        $this->status = 'pending';
        $this->error = null;
        $this->failed_at = null;
        $this->started_at = null;
        $this->timeout_at = null;
        $this->save();
        
        return true;
    }
    
    /**
     * Ottiene il prossimo tentativo
     */
    public function getNextRetry(): int
    {
        return $this->retry_count + 1;
    }
    
    /**
     * Verifica se il passaggio ha raggiunto il limite di tentativi
     */
    public function hasReachedMaxRetries(): bool
    {
        return $this->retry_count >= $this->max_retries;
    }
    
    /**
     * Ottiene il tempo di attesa per il prossimo tentativo
     */
    public function getRetryDelay(): int
    {
        // Backoff esponenziale: 2^retry_count minuti
        return pow(2, $this->retry_count) * 60;
    }
    
    /**
     * Verifica se il passaggio è idempotente
     */
    public function isIdempotent(): bool
    {
        $idempotentSteps = [
            'validate_user',
            'validate_order',
            'send_notification',
            'send_cancellation_notification'
        ];
        
        return in_array($this->step_name, $idempotentSteps);
    }
    
    /**
     * Verifica se il passaggio è critico
     */
    public function isCritical(): bool
    {
        $criticalSteps = [
            'process_payment',
            'refund_payment',
            'reserve_inventory',
            'release_inventory'
        ];
        
        return in_array($this->step_name, $criticalSteps);
    }
    
    /**
     * Ottiene il tipo di passaggio
     */
    public function getStepType(): string
    {
        if (str_contains($this->step_name, 'validate')) {
            return 'validation';
        } elseif (str_contains($this->step_name, 'process') || str_contains($this->step_name, 'refund')) {
            return 'payment';
        } elseif (str_contains($this->step_name, 'reserve') || str_contains($this->step_name, 'release')) {
            return 'inventory';
        } elseif (str_contains($this->step_name, 'create') || str_contains($this->step_name, 'delete') || str_contains($this->step_name, 'cancel')) {
            return 'data';
        } elseif (str_contains($this->step_name, 'send')) {
            return 'notification';
        } else {
            return 'unknown';
        }
    }
    
    /**
     * Ottiene il livello di priorità del passaggio
     */
    public function getPriority(): int
    {
        $priorities = [
            'validate_user' => 1,
            'validate_order' => 1,
            'reserve_inventory' => 2,
            'create_order' => 3,
            'process_payment' => 4,
            'send_notification' => 5,
            'refund_payment' => 2,
            'release_inventory' => 3,
            'cancel_order' => 4,
            'send_cancellation_notification' => 5
        ];
        
        return $priorities[$this->step_name] ?? 5;
    }
}
