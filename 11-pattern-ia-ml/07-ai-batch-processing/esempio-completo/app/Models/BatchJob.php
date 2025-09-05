<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'total_requests',
        'processed_requests',
        'failed_requests',
        'provider',
        'model',
        'batch_size',
        'priority',
        'scheduled_at',
        'completed_at',
        'metadata',
        'error_message',
        'processing_time_seconds',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'processing_time_seconds' => 'float',
    ];

    // Stati possibili del batch
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    // Priorità possibili
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Relazione con le richieste del batch
     */
    public function requests(): HasMany
    {
        return $this->hasMany(BatchRequest::class);
    }

    /**
     * Verifica se il batch è completato
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica se il batch è in elaborazione
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Verifica se il batch è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se il batch è fallito
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Verifica se il batch è cancellato
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Calcola la percentuale di completamento
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_requests === 0) {
            return 0;
        }

        return round(($this->processed_requests / $this->total_requests) * 100, 2);
    }

    /**
     * Calcola il tempo di elaborazione rimanente stimato
     */
    public function getEstimatedTimeRemaining(): ?int
    {
        if (!$this->isProcessing() || $this->processed_requests === 0) {
            return null;
        }

        $elapsedTime = now()->diffInSeconds($this->updated_at);
        $requestsPerSecond = $this->processed_requests / $elapsedTime;
        $remainingRequests = $this->total_requests - $this->processed_requests;

        return (int) round($remainingRequests / $requestsPerSecond);
    }

    /**
     * Calcola il tempo di elaborazione totale
     */
    public function getTotalProcessingTime(): ?int
    {
        if (!$this->completed_at || !$this->scheduled_at) {
            return null;
        }

        return $this->scheduled_at->diffInSeconds($this->completed_at);
    }

    /**
     * Calcola il throughput (richieste al secondo)
     */
    public function getThroughput(): ?float
    {
        $totalTime = $this->getTotalProcessingTime();
        
        if (!$totalTime || $totalTime === 0) {
            return null;
        }

        return round($this->processed_requests / $totalTime, 2);
    }

    /**
     * Calcola il tasso di successo
     */
    public function getSuccessRate(): float
    {
        if ($this->total_requests === 0) {
            return 0;
        }

        $successfulRequests = $this->processed_requests - $this->failed_requests;
        return round(($successfulRequests / $this->total_requests) * 100, 2);
    }

    /**
     * Verifica se il batch può essere processato
     */
    public function canBeProcessed(): bool
    {
        return $this->isPending() && 
               $this->scheduled_at <= now() && 
               $this->requests()->where('status', BatchRequest::STATUS_PENDING)->exists();
    }

    /**
     * Verifica se il batch è pronto per essere completato
     */
    public function isReadyForCompletion(): bool
    {
        $pendingRequests = $this->requests()
            ->where('status', BatchRequest::STATUS_PENDING)
            ->count();

        return $this->isProcessing() && $pendingRequests === 0;
    }

    /**
     * Aggiorna le statistiche del batch
     */
    public function updateStatistics(): void
    {
        $this->update([
            'processed_requests' => $this->requests()
                ->whereIn('status', [BatchRequest::STATUS_COMPLETED, BatchRequest::STATUS_FAILED])
                ->count(),
            'failed_requests' => $this->requests()
                ->where('status', BatchRequest::STATUS_FAILED)
                ->count(),
        ]);
    }

    /**
     * Marca il batch come completato
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'processing_time_seconds' => $this->getTotalProcessingTime(),
        ]);
    }

    /**
     * Marca il batch come fallito
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Marca il batch come in elaborazione
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Cancella il batch
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope per batch in attesa
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope per batch in elaborazione
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope per batch completati
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope per batch falliti
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope per batch pronti per l'elaborazione
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->pending()
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope per provider specifico
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope per priorità specifica
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope per batch con errori
     */
    public function scopeWithErrors($query)
    {
        return $query->where('failed_requests', '>', 0);
    }

    /**
     * Scope per batch senza errori
     */
    public function scopeWithoutErrors($query)
    {
        return $query->where('failed_requests', 0);
    }
}
