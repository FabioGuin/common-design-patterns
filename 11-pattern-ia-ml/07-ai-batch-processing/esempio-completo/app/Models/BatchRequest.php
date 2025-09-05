<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_job_id',
        'input',
        'expected_output',
        'actual_output',
        'status',
        'priority',
        'error_message',
        'processing_time_ms',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'processing_time_ms' => 'integer',
    ];

    // Stati possibili della richiesta
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // Priorità possibili
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Relazione con il batch job
     */
    public function batchJob(): BelongsTo
    {
        return $this->belongsTo(BatchJob::class);
    }

    /**
     * Verifica se la richiesta è completata
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica se la richiesta è in elaborazione
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Verifica se la richiesta è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se la richiesta è fallita
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Marca la richiesta come completata
     */
    public function markAsCompleted(string $output, int $processingTimeMs = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'actual_output' => $output,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Marca la richiesta come fallita
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Marca la richiesta come in elaborazione
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Scope per richieste in attesa
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope per richieste in elaborazione
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope per richieste completate
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope per richieste fallite
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope per priorità specifica
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope per richieste con errori
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status', self::STATUS_FAILED)
                    ->whereNotNull('error_message');
    }
}
