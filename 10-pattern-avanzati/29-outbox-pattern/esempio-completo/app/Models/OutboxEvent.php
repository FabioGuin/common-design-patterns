<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutboxEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'event_data',
        'aggregate_id',
        'status',
        'retry_count',
        'scheduled_at',
        'processing_started_at',
        'published_at',
        'failed_at',
        'error_message'
    ];

    protected $casts = [
        'event_data' => 'array',
        'scheduled_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'published_at' => 'datetime',
        'failed_at' => 'datetime'
    ];

    /**
     * Scope per eventi pendenti
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope per eventi in processing
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope per eventi pubblicati
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope per eventi falliti
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope per eventi pronti per essere processati
     */
    public function scopeReadyForProcessing($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope per eventi che necessitano retry
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '<=', now())
                    ->where('retry_count', '<', config('outbox.max_retries', 3));
    }

    /**
     * Verifica se l'evento è pronto per essere processato
     */
    public function isReadyForProcessing(): bool
    {
        return $this->status === 'pending' && 
               $this->scheduled_at <= now();
    }

    /**
     * Verifica se l'evento può essere ritentato
     */
    public function canRetry(): bool
    {
        return $this->status === 'pending' && 
               $this->retry_count < config('outbox.max_retries', 3);
    }

    /**
     * Verifica se l'evento è in processing da troppo tempo
     */
    public function isStuck(): bool
    {
        if ($this->status !== 'processing' || !$this->processing_started_at) {
            return false;
        }

        $timeout = config('outbox.processing_timeout', 300); // 5 minuti
        return $this->processing_started_at->addSeconds($timeout) < now();
    }

    /**
     * Calcola il prossimo tentativo con backoff esponenziale
     */
    public function getNextRetryAt(): \Carbon\Carbon
    {
        $baseDelay = config('outbox.base_retry_delay', 60); // 1 minuto
        $delay = $baseDelay * pow(2, $this->retry_count);
        
        return now()->addSeconds($delay);
    }

    /**
     * Ottiene un identificatore univoco per l'evento
     */
    public function getEventId(): string
    {
        return "{$this->event_type}_{$this->id}_{$this->created_at->timestamp}";
    }

    /**
     * Ottiene i dati dell'evento formattati per la pubblicazione
     */
    public function getFormattedEventData(): array
    {
        return [
            'event_id' => $this->getEventId(),
            'event_type' => $this->event_type,
            'event_data' => $this->event_data,
            'aggregate_id' => $this->aggregate_id,
            'created_at' => $this->created_at->toISOString(),
            'metadata' => [
                'retry_count' => $this->retry_count,
                'original_created_at' => $this->created_at->toISOString()
            ]
        ];
    }

    /**
     * Relazione con l'aggregato (se applicabile)
     */
    public function aggregate()
    {
        // In un sistema reale, potresti avere una relazione polimorfica
        // return $this->morphTo('aggregate');
        return null;
    }

    /**
     * Ottiene statistiche per tipo di evento
     */
    public static function getStatsByEventType(): array
    {
        return static::selectRaw('
                event_type,
                status,
                COUNT(*) as count
            ')
            ->groupBy('event_type', 'status')
            ->orderBy('event_type')
            ->orderBy('status')
            ->get()
            ->groupBy('event_type')
            ->map(function ($events) {
                return $events->pluck('count', 'status');
            })
            ->toArray();
    }

    /**
     * Ottiene eventi vecchi per pulizia
     */
    public static function getOldEvents(int $daysOld = 7): \Illuminate\Database\Eloquent\Collection
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return static::where('status', 'published')
            ->where('published_at', '<', $cutoffDate)
            ->get();
    }
}
