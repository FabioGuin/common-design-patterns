<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InboxEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'event_data',
        'status',
        'retry_count',
        'scheduled_at',
        'processing_started_at',
        'processed_at',
        'failed_at',
        'error_message'
    ];

    protected $casts = [
        'event_data' => 'array',
        'scheduled_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processed_at' => 'datetime',
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
     * Scope per eventi processati
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
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
                    ->where('retry_count', '<', config('inbox.max_retries', 3));
    }

    /**
     * Scope per eventi stuck
     */
    public function scopeStuck($query)
    {
        $timeout = config('inbox.processing_timeout', 300);
        $cutoffTime = now()->subSeconds($timeout);
        
        return $query->where('status', 'processing')
                    ->where('processing_started_at', '<', $cutoffTime);
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
               $this->retry_count < config('inbox.max_retries', 3);
    }

    /**
     * Verifica se l'evento è in processing da troppo tempo
     */
    public function isStuck(): bool
    {
        if ($this->status !== 'processing' || !$this->processing_started_at) {
            return false;
        }

        $timeout = config('inbox.processing_timeout', 300); // 5 minuti
        return $this->processing_started_at->addSeconds($timeout) < now();
    }

    /**
     * Calcola il prossimo tentativo con backoff esponenziale
     */
    public function getNextRetryAt(): \Carbon\Carbon
    {
        $baseDelay = config('inbox.base_retry_delay', 60); // 1 minuto
        $delay = $baseDelay * pow(2, $this->retry_count);
        
        return now()->addSeconds($delay);
    }

    /**
     * Ottiene un identificatore univoco per l'evento
     */
    public function getUniqueId(): string
    {
        return "{$this->event_type}_{$this->event_id}_{$this->created_at->timestamp}";
    }

    /**
     * Ottiene i dati dell'evento formattati per il processing
     */
    public function getFormattedEventData(): array
    {
        return [
            'event_id' => $this->event_id,
            'event_type' => $this->event_type,
            'event_data' => $this->event_data,
            'created_at' => $this->created_at->toISOString(),
            'metadata' => [
                'retry_count' => $this->retry_count,
                'original_created_at' => $this->created_at->toISOString()
            ]
        ];
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
        
        return static::where('status', 'processed')
            ->where('processed_at', '<', $cutoffDate)
            ->get();
    }

    /**
     * Ottiene eventi duplicati
     */
    public static function getDuplicateEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return static::select('event_id')
            ->groupBy('event_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    /**
     * Ottiene eventi per un periodo specifico
     */
    public static function getEventsByDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi per tipo
     */
    public static function getEventsByType(string $eventType): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verifica se un evento è già stato processato
     */
    public static function isEventProcessed(string $eventId): bool
    {
        return static::where('event_id', $eventId)
            ->whereIn('status', ['processed', 'failed'])
            ->exists();
    }

    /**
     * Ottiene il tempo di processing medio
     */
    public static function getAverageProcessingTime(): float
    {
        $events = static::where('status', 'processed')
            ->whereNotNull('processing_started_at')
            ->whereNotNull('processed_at')
            ->get();

        if ($events->isEmpty()) {
            return 0;
        }

        $totalTime = $events->sum(function ($event) {
            return $event->processing_started_at->diffInSeconds($event->processed_at);
        });

        return $totalTime / $events->count();
    }

    /**
     * Ottiene il tasso di successo
     */
    public static function getSuccessRate(): float
    {
        $total = static::count();
        
        if ($total === 0) {
            return 0;
        }

        $processed = static::where('status', 'processed')->count();
        
        return ($processed / $total) * 100;
    }
}
