<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modello Saga per il Saga Orchestration Pattern
 * 
 * Questo modello rappresenta una saga completa con tutti i suoi passaggi
 * e gestisce lo stato della transazione distribuita.
 */
class Saga extends Model
{
    protected $fillable = [
        'type',
        'status',
        'data',
        'current_step',
        'total_steps',
        'started_at',
        'completed_at',
        'timeout_at',
        'error'
    ];
    
    protected $casts = [
        'data' => 'array',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'timeout_at' => 'datetime'
    ];
    
    /**
     * Relazione con i passaggi della saga
     */
    public function steps(): HasMany
    {
        return $this->hasMany(SagaStep::class);
    }
    
    /**
     * Verifica se la saga è completata
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    /**
     * Verifica se la saga è fallita
     */
    public function isFailed(): bool
    {
        return $this->status === 'compensated';
    }
    
    /**
     * Verifica se la saga è in esecuzione
     */
    public function isRunning(): bool
    {
        return in_array($this->status, ['started', 'compensating']);
    }
    
    /**
     * Verifica se la saga è scaduta
     */
    public function isExpired(): bool
    {
        return $this->timeout_at && $this->timeout_at->isPast();
    }
    
    /**
     * Ottiene il progresso della saga in percentuale
     */
    public function getProgress(): float
    {
        if ($this->total_steps <= 0) {
            return 0;
        }
        
        return round(($this->current_step / $this->total_steps) * 100, 2);
    }
    
    /**
     * Ottiene i passaggi completati
     */
    public function getCompletedSteps()
    {
        return $this->steps()->where('status', 'completed')->get();
    }
    
    /**
     * Ottiene i passaggi falliti
     */
    public function getFailedSteps()
    {
        return $this->steps()->where('status', 'failed')->get();
    }
    
    /**
     * Ottiene i passaggi in sospeso
     */
    public function getPendingSteps()
    {
        return $this->steps()->where('status', 'pending')->get();
    }
    
    /**
     * Ottiene le statistiche della saga
     */
    public function getStats(): array
    {
        $steps = $this->steps;
        
        return [
            'saga_id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'current_step' => $this->current_step,
            'total_steps' => $this->total_steps,
            'progress' => $this->getProgress(),
            'completed_steps' => $steps->where('status', 'completed')->count(),
            'failed_steps' => $steps->where('status', 'failed')->count(),
            'pending_steps' => $steps->where('status', 'pending')->count(),
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'duration' => $this->completed_at ? 
                $this->started_at->diffInSeconds($this->completed_at) : null,
            'is_expired' => $this->isExpired(),
            'error' => $this->error
        ];
    }
    
    /**
     * Ottiene la durata della saga
     */
    public function getDuration(): ?int
    {
        if (!$this->started_at) {
            return null;
        }
        
        $endTime = $this->completed_at ?? now();
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
     * Verifica se la saga può essere riavviata
     */
    public function canBeRestarted(): bool
    {
        return in_array($this->status, ['failed', 'compensated']) && !$this->isExpired();
    }
    
    /**
     * Riavvia la saga
     */
    public function restart(): bool
    {
        if (!$this->canBeRestarted()) {
            return false;
        }
        
        $this->status = 'started';
        $this->current_step = 0;
        $this->error = null;
        $this->started_at = now();
        $this->timeout_at = now()->addMinutes(5);
        $this->save();
        
        return true;
    }
    
    /**
     * Cancella la saga
     */
    public function cancel(): bool
    {
        if ($this->isCompleted()) {
            return false;
        }
        
        $this->status = 'cancelled';
        $this->save();
        
        return true;
    }
    
    /**
     * Ottiene i passaggi in ordine di esecuzione
     */
    public function getStepsInOrder()
    {
        return $this->steps()->orderBy('id')->get();
    }
    
    /**
     * Ottiene i passaggi in ordine di compensazione
     */
    public function getStepsInCompensationOrder()
    {
        return $this->steps()->orderBy('id', 'desc')->get();
    }
    
    /**
     * Verifica se tutti i passaggi sono completati
     */
    public function allStepsCompleted(): bool
    {
        return $this->steps()->where('status', '!=', 'completed')->count() === 0;
    }
    
    /**
     * Verifica se ci sono passaggi falliti
     */
    public function hasFailedSteps(): bool
    {
        return $this->steps()->where('status', 'failed')->count() > 0;
    }
    
    /**
     * Ottiene il prossimo passaggio da eseguire
     */
    public function getNextStep(): ?SagaStep
    {
        return $this->steps()->where('status', 'pending')->orderBy('id')->first();
    }
    
    /**
     * Ottiene l'ultimo passaggio eseguito
     */
    public function getLastStep(): ?SagaStep
    {
        return $this->steps()->orderBy('id', 'desc')->first();
    }
}
