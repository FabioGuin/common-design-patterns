<?php

namespace App\Jobs;

use App\Models\OutboxEvent;
use App\Services\EventPublisherService;
use App\Services\OutboxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOutboxEventsJob implements ShouldQueue
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
    public function handle(EventPublisherService $eventPublisher, OutboxService $outboxService): void
    {
        Log::info('Starting outbox events processing');

        $processedCount = 0;
        $failedCount = 0;

        // Recupera eventi pronti per essere processati
        $events = $outboxService->getPendingEvents(50); // Processa max 50 eventi per volta

        if ($events->isEmpty()) {
            Log::info('No pending events to process');
            return;
        }

        Log::info('Found events to process', ['count' => $events->count()]);

        foreach ($events as $event) {
            try {
                $this->processEvent($event, $eventPublisher, $outboxService);
                $processedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to process outbox event', [
                    'event_id' => $event->id,
                    'event_type' => $event->event_type,
                    'error' => $e->getMessage()
                ]);
                $failedCount++;
            }
        }

        Log::info('Outbox events processing completed', [
            'processed' => $processedCount,
            'failed' => $failedCount,
            'total' => $events->count()
        ]);
    }

    /**
     * Processa un singolo evento
     */
    private function processEvent(
        OutboxEvent $event, 
        EventPublisherService $eventPublisher, 
        OutboxService $outboxService
    ): void {
        // Verifica se l'evento è già in processing da troppo tempo
        if ($event->isStuck()) {
            Log::warning('Event is stuck, resetting to pending', [
                'event_id' => $event->id,
                'processing_started_at' => $event->processing_started_at
            ]);
            
            $outboxService->markEventAsFailed($event, 'Event processing timeout');
            return;
        }

        // Marca l'evento come in processing
        $outboxService->markEventAsProcessing($event);

        Log::info('Processing outbox event', [
            'event_id' => $event->id,
            'event_type' => $event->event_type,
            'retry_count' => $event->retry_count
        ]);

        // Pubblica l'evento
        $success = $eventPublisher->publishEvent(
            $event->event_type, 
            $event->getFormattedEventData()
        );

        if ($success) {
            // Marca come pubblicato
            $outboxService->markEventAsPublished($event);
            
            Log::info('Event published successfully', [
                'event_id' => $event->id,
                'event_type' => $event->event_type
            ]);
        } else {
            // Marca come fallito e programma retry
            $outboxService->markEventAsFailed($event, 'Publishing failed');
            
            Log::warning('Event publishing failed, will retry', [
                'event_id' => $event->id,
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
        Log::error('ProcessOutboxEventsJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['outbox', 'events', 'processing'];
    }
}
