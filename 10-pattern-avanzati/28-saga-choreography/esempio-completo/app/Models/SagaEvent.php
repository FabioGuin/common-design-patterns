<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modello SagaEvent per il Saga Choreography Pattern
 * 
 * Questo modello rappresenta un evento di una saga e gestisce
 * lo stato e i metadati dell'evento.
 */
class SagaEvent extends Model
{
    protected $fillable = [
        'event_type',
        'event_data',
        'metadata',
        'status',
        'published_at',
        'processed_at',
        'failed_at',
        'retry_count',
        'max_retries',
        'error_message'
    ];
    
    protected $casts = [
        'event_data' => 'array',
        'metadata' => 'array',
        'published_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'retry_count' => 'integer',
        'max_retries' => 'integer'
    ];
    
    /**
     * Verifica se l'evento è pubblicato
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
    
    /**
     * Verifica se l'evento è processato
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }
    
    /**
     * Verifica se l'evento è fallito
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
    
    /**
     * Verifica se l'evento è in coda
     */
    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }
    
    /**
     * Verifica se l'evento può essere riprovato
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < $this->max_retries;
    }
    
    /**
     * Verifica se l'evento è scaduto
     */
    public function isExpired(): bool
    {
        if (!$this->metadata || !isset($this->metadata['expires_at'])) {
            return false;
        }
        
        return now()->parse($this->metadata['expires_at'])->isPast();
    }
    
    /**
     * Ottiene la durata dell'evento
     */
    public function getDuration(): ?int
    {
        if (!$this->published_at) {
            return null;
        }
        
        $endTime = $this->processed_at ?? $this->failed_at ?? now();
        return $this->published_at->diffInSeconds($endTime);
    }
    
    /**
     * Ottiene le statistiche dell'evento
     */
    public function getStats(): array
    {
        return [
            'event_id' => $this->id,
            'event_type' => $this->event_type,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'processed_at' => $this->processed_at,
            'failed_at' => $this->failed_at,
            'duration' => $this->getDuration(),
            'retry_count' => $this->retry_count,
            'max_retries' => $this->max_retries,
            'can_retry' => $this->canRetry(),
            'is_expired' => $this->isExpired(),
            'error_message' => $this->error_message
        ];
    }
    
    /**
     * Marca l'evento come processato
     */
    public function markAsProcessed(): void
    {
        $this->status = 'processed';
        $this->processed_at = now();
        $this->save();
    }
    
    /**
     * Marca l'evento come fallito
     */
    public function markAsFailed(string $error, bool $incrementRetry = true): void
    {
        $this->status = 'failed';
        $this->error_message = $error;
        $this->failed_at = now();
        
        if ($incrementRetry) {
            $this->retry_count++;
        }
        
        $this->save();
    }
    
    /**
     * Riprova l'evento
     */
    public function retry(): bool
    {
        if (!$this->canRetry()) {
            return false;
        }
        
        $this->status = 'queued';
        $this->error_message = null;
        $this->failed_at = null;
        $this->save();
        
        return true;
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
     * Verifica se l'evento è idempotente
     */
    public function isIdempotent(): bool
    {
        $idempotentEvents = [
            'UserValidated',
            'UserValidationFailed',
            'NotificationSent',
            'NotificationCancelled'
        ];
        
        return in_array($this->event_type, $idempotentEvents);
    }
    
    /**
     * Verifica se l'evento è critico
     */
    public function isCritical(): bool
    {
        $criticalEvents = [
            'PaymentProcessed',
            'PaymentRefunded',
            'InventoryReserved',
            'InventoryReleased'
        ];
        
        return in_array($this->event_type, $criticalEvents);
    }
    
    /**
     * Ottiene il tipo di evento
     */
    public function getEventCategory(): string
    {
        if (str_contains($this->event_type, 'User')) {
            return 'user';
        } elseif (str_contains($this->event_type, 'Inventory')) {
            return 'inventory';
        } elseif (str_contains($this->event_type, 'Order')) {
            return 'order';
        } elseif (str_contains($this->event_type, 'Payment')) {
            return 'payment';
        } elseif (str_contains($this->event_type, 'Notification')) {
            return 'notification';
        } else {
            return 'unknown';
        }
    }
    
    /**
     * Ottiene il livello di priorità dell'evento
     */
    public function getPriority(): int
    {
        $priorities = [
            'UserValidated' => 1,
            'UserValidationFailed' => 1,
            'InventoryReserved' => 2,
            'InventoryReleased' => 2,
            'OrderCreated' => 3,
            'OrderCancelled' => 3,
            'PaymentProcessed' => 4,
            'PaymentRefunded' => 4,
            'NotificationSent' => 5,
            'NotificationCancelled' => 5
        ];
        
        return $priorities[$this->event_type] ?? 5;
    }
    
    /**
     * Ottiene gli eventi per tipo
     */
    public static function getByType(string $eventType)
    {
        return static::where('event_type', $eventType)
            ->orderBy('published_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene gli eventi per utente
     */
    public static function getByUser(int $userId)
    {
        return static::whereJsonContains('event_data->user_id', $userId)
            ->orderBy('published_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene gli eventi falliti
     */
    public static function getFailed()
    {
        return static::where('status', 'failed')
            ->orderBy('failed_at', 'desc')
            ->get();
    }
    
    /**
     * Ottiene gli eventi in coda
     */
    public static function getQueued()
    {
        return static::where('status', 'queued')
            ->orderBy('published_at', 'asc')
            ->get();
    }
    
    /**
     * Ottiene gli eventi che possono essere riprovati
     */
    public static function getRetryable()
    {
        return static::where('status', 'failed')
            ->whereRaw('retry_count < max_retries')
            ->orderBy('failed_at', 'asc')
            ->get();
    }
    
    /**
     * Ottiene gli eventi scaduti
     */
    public static function getExpired()
    {
        return static::where('status', 'queued')
            ->whereJsonContains('metadata->expires_at', now()->toISOString())
            ->get();
    }
    
    /**
     * Pulisce gli eventi vecchi
     */
    public static function cleanupOld(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        return static::where('published_at', '<', $cutoffDate)
            ->whereIn('status', ['processed', 'failed'])
            ->delete();
    }
}
