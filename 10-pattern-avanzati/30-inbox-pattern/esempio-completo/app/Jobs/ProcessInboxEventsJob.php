<?php

namespace App\Jobs;

use App\Models\InboxEvent;
use App\Services\EventConsumerService;
use App\Services\InboxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInboxEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minuti
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(EventConsumerService $eventConsumer, InboxService $inboxService): void
    {
        Log::info('Starting inbox events processing');

        $processedCount = 0;
        $failedCount = 0;

        // Ripristina eventi stuck prima di processare
        $restoredCount = $inboxService->restoreStuckEvents();
        if ($restoredCount > 0) {
            Log::info('Restored stuck events', ['count' => $restoredCount]);
        }

        // Recupera eventi pronti per essere processati
        $events = $inboxService->getPendingEvents(50); // Processa max 50 eventi per volta

        if ($events->isEmpty()) {
            Log::info('No pending events to process');
            return;
        }

        Log::info('Found events to process', ['count' => $events->count()]);

        foreach ($events as $event) {
            try {
                $this->processEvent($event, $eventConsumer, $inboxService);
                $processedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to process inbox event', [
                    'event_id' => $event->event_id,
                    'event_type' => $event->event_type,
                    'error' => $e->getMessage()
                ]);
                $failedCount++;
            }
        }

        Log::info('Inbox events processing completed', [
            'processed' => $processedCount,
            'failed' => $failedCount,
            'total' => $events->count()
        ]);
    }

    /**
     * Processa un singolo evento
     */
    private function processEvent(
        InboxEvent $event, 
        EventConsumerService $eventConsumer, 
        InboxService $inboxService
    ): void {
        // Verifica se l'evento è già in processing da troppo tempo
        if ($inboxService->isEventStuck($event)) {
            Log::warning('Event is stuck, resetting to pending', [
                'event_id' => $event->event_id,
                'processing_started_at' => $event->processing_started_at
            ]);
            
            $inboxService->markEventAsFailed($event, 'Event processing timeout');
            return;
        }

        Log::info('Processing inbox event', [
            'event_id' => $event->event_id,
            'event_type' => $event->event_type,
            'retry_count' => $event->retry_count
        ]);

        // Processa l'evento usando il servizio di consumo
        $success = $inboxService->processEvent($event, function ($eventData) use ($eventConsumer) {
            return $eventConsumer->consumeEvent($eventData);
        });

        if (!$success) {
            Log::warning('Event processing failed, will retry', [
                'event_id' => $event->event_id,
                'event_type' => $event->event_type,
                'retry_count' => $event->retry_count + 1
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessInboxEventsJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['inbox', 'events', 'processing'];
    }
}
