<?php

namespace App\Services;

use App\Models\InboxEvent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboxService
{
    /**
     * Riceve un evento e lo inserisce nell'inbox se non esiste già
     */
    public function receiveEvent(string $eventId, string $eventType, array $eventData): InboxEvent
    {
        return DB::transaction(function () use ($eventId, $eventType, $eventData) {
            // Verifica se l'evento è già stato ricevuto
            $existingEvent = InboxEvent::where('event_id', $eventId)->first();
            
            if ($existingEvent) {
                Log::info('Event already received, skipping', [
                    'event_id' => $eventId,
                    'event_type' => $eventType
                ]);
                return $existingEvent;
            }

            // Inserisce il nuovo evento nell'inbox
            $inboxEvent = InboxEvent::create([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'event_data' => $eventData,
                'status' => 'pending',
                'retry_count' => 0,
                'scheduled_at' => now()
            ]);

            Log::info('Event received and stored in inbox', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'inbox_id' => $inboxEvent->id
            ]);

            return $inboxEvent;
        });
    }

    /**
     * Recupera eventi pendenti dall'inbox
     */
    public function getPendingEvents(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return InboxEvent::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Marca un evento come in processing
     */
    public function markEventAsProcessing(InboxEvent $event): bool
    {
        return $event->update([
            'status' => 'processing',
            'processing_started_at' => now()
        ]);
    }

    /**
     * Marca un evento come processato con successo
     */
    public function markEventAsProcessed(InboxEvent $event): bool
    {
        return $event->update([
            'status' => 'processed',
            'processed_at' => now()
        ]);
    }

    /**
     * Marca un evento come fallito e programma un retry
     */
    public function markEventAsFailed(InboxEvent $event, string $error = null): bool
    {
        $retryCount = $event->retry_count + 1;
        $maxRetries = config('inbox.max_retries', 3);
        
        if ($retryCount >= $maxRetries) {
            // Troppi tentativi, marca come definitivamente fallito
            return $event->update([
                'status' => 'failed',
                'retry_count' => $retryCount,
                'error_message' => $error,
                'failed_at' => now()
            ]);
        }
        
        // Programma un retry con backoff esponenziale
        $delay = pow(2, $retryCount) * 60; // 2, 4, 8 minuti
        $scheduledAt = now()->addSeconds($delay);
        
        return $event->update([
            'status' => 'pending',
            'retry_count' => $retryCount,
            'error_message' => $error,
            'scheduled_at' => $scheduledAt
        ]);
    }

    /**
     * Verifica se un evento è già stato processato
     */
    public function isEventProcessed(string $eventId): bool
    {
        return InboxEvent::where('event_id', $eventId)
            ->whereIn('status', ['processed', 'failed'])
            ->exists();
    }

    /**
     * Verifica se un evento è in processing da troppo tempo
     */
    public function isEventStuck(InboxEvent $event): bool
    {
        if ($event->status !== 'processing' || !$event->processing_started_at) {
            return false;
        }

        $timeout = config('inbox.processing_timeout', 300); // 5 minuti
        return $event->processing_started_at->addSeconds($timeout) < now();
    }

    /**
     * Ripristina eventi stuck
     */
    public function restoreStuckEvents(): int
    {
        $timeout = config('inbox.processing_timeout', 300);
        $cutoffTime = now()->subSeconds($timeout);
        
        $stuckEvents = InboxEvent::where('status', 'processing')
            ->where('processing_started_at', '<', $cutoffTime)
            ->get();

        $restoredCount = 0;
        foreach ($stuckEvents as $event) {
            $this->markEventAsFailed($event, 'Processing timeout - event was stuck');
            $restoredCount++;
        }

        if ($restoredCount > 0) {
            Log::warning('Restored stuck events', ['count' => $restoredCount]);
        }

        return $restoredCount;
    }

    /**
     * Pulisce eventi processati più vecchi di X giorni
     */
    public function cleanupProcessedEvents(int $daysOld = 7): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return InboxEvent::where('status', 'processed')
            ->where('processed_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Ottiene statistiche dell'inbox
     */
    public function getInboxStats(): array
    {
        return [
            'pending' => InboxEvent::where('status', 'pending')->count(),
            'processing' => InboxEvent::where('status', 'processing')->count(),
            'processed' => InboxEvent::where('status', 'processed')->count(),
            'failed' => InboxEvent::where('status', 'failed')->count(),
            'total' => InboxEvent::count()
        ];
    }

    /**
     * Ottiene eventi per tipo
     */
    public function getEventsByType(string $eventType): \Illuminate\Database\Eloquent\Collection
    {
        return InboxEvent::where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi per un periodo specifico
     */
    public function getEventsByDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return InboxEvent::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ottiene eventi duplicati (stesso event_id)
     */
    public function getDuplicateEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return InboxEvent::select('event_id')
            ->groupBy('event_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }

    /**
     * Processa un singolo evento
     */
    public function processEvent(InboxEvent $event, callable $processor): bool
    {
        try {
            Log::info('Processing inbox event', [
                'event_id' => $event->event_id,
                'event_type' => $event->event_type,
                'inbox_id' => $event->id
            ]);

            // Marca come in processing
            $this->markEventAsProcessing($event);

            // Processa l'evento
            $result = $processor($event->event_data);

            if ($result) {
                // Marca come processato
                $this->markEventAsProcessed($event);
                
                Log::info('Event processed successfully', [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type
                ]);
                
                return true;
            } else {
                // Marca come fallito
                $this->markEventAsFailed($event, 'Processor returned false');
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Error processing inbox event', [
                'event_id' => $event->event_id,
                'event_type' => $event->event_type,
                'error' => $e->getMessage()
            ]);

            $this->markEventAsFailed($event, $e->getMessage());
            return false;
        }
    }
}
