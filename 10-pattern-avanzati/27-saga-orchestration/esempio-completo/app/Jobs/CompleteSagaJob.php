<?php

namespace App\Jobs;

use App\Models\Saga;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Job per completare una saga
 * 
 * Questo job gestisce il completamento di una saga,
 * sia in caso di successo che di compensazione.
 */
class CompleteSagaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sagaId;
    public string $finalStatus;
    public int $tries = 1;
    public int $timeout = 60;

    /**
     * Crea una nuova istanza del job
     */
    public function __construct(int $sagaId, string $finalStatus)
    {
        $this->sagaId = $sagaId;
        $this->finalStatus = $finalStatus;
    }

    /**
     * Esegue il job
     */
    public function handle(): void
    {
        $saga = Saga::find($this->sagaId);

        if (!$saga) {
            Log::error('Saga not found for completion', [
                'saga_id' => $this->sagaId
            ]);
            return;
        }

        try {
            Log::info('Completing saga', [
                'saga_id' => $this->sagaId,
                'final_status' => $this->finalStatus
            ]);

            // Aggiorna lo stato finale della saga
            $saga->status = $this->finalStatus;
            $saga->completed_at = now();
            $saga->save();

            // Esegue le azioni finali in base allo stato
            if ($this->finalStatus === 'completed') {
                $this->handleSuccessfulCompletion($saga);
            } elseif ($this->finalStatus === 'compensated') {
                $this->handleCompensatedCompletion($saga);
            }

            Log::info('Saga completed successfully', [
                'saga_id' => $this->sagaId,
                'final_status' => $this->finalStatus
            ]);

        } catch (Exception $e) {
            Log::error('Failed to complete saga', [
                'saga_id' => $this->sagaId,
                'final_status' => $this->finalStatus,
                'error' => $e->getMessage()
            ]);

            // Marca la saga come fallita
            $saga->status = 'failed';
            $saga->error = $e->getMessage();
            $saga->save();
        }
    }

    /**
     * Gestisce il completamento con successo
     */
    private function handleSuccessfulCompletion(Saga $saga): void
    {
        Log::info('Saga completed successfully', [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'duration' => $saga->getDuration()
        ]);

        // Invia notifica di successo
        $this->sendSuccessNotification($saga);

        // Aggiorna le metriche
        $this->updateSuccessMetrics($saga);

        // Pulisce i dati temporanei
        $this->cleanupTemporaryData($saga);
    }

    /**
     * Gestisce il completamento con compensazione
     */
    private function handleCompensatedCompletion(Saga $saga): void
    {
        Log::warning('Saga completed with compensation', [
            'saga_id' => $saga->id,
            'type' => $saga->type,
            'duration' => $saga->getDuration()
        ]);

        // Invia notifica di fallimento
        $this->sendFailureNotification($saga);

        // Aggiorna le metriche
        $this->updateFailureMetrics($saga);

        // Pulisce i dati temporanei
        $this->cleanupTemporaryData($saga);
    }

    /**
     * Invia notifica di successo
     */
    private function sendSuccessNotification(Saga $saga): void
    {
        try {
            $notificationService = new \App\Services\NotificationService();
            
            $message = $this->getSuccessMessage($saga);
            $notificationService->sendNotification(
                $saga->data['user_id'] ?? null,
                $message
            );

            Log::info('Success notification sent', [
                'saga_id' => $saga->id,
                'message' => $message
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send success notification', [
                'saga_id' => $saga->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Invia notifica di fallimento
     */
    private function sendFailureNotification(Saga $saga): void
    {
        try {
            $notificationService = new \App\Services\NotificationService();
            
            $message = $this->getFailureMessage($saga);
            $notificationService->sendNotification(
                $saga->data['user_id'] ?? null,
                $message
            );

            Log::info('Failure notification sent', [
                'saga_id' => $saga->id,
                'message' => $message
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send failure notification', [
                'saga_id' => $saga->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ottiene il messaggio di successo
     */
    private function getSuccessMessage(Saga $saga): string
    {
        switch ($saga->type) {
            case 'create_order':
                return 'Your order has been successfully created and processed.';
            
            case 'cancel_order':
                return 'Your order has been successfully cancelled and refunded.';
            
            default:
                return 'Your request has been successfully processed.';
        }
    }

    /**
     * Ottiene il messaggio di fallimento
     */
    private function getFailureMessage(Saga $saga): string
    {
        switch ($saga->type) {
            case 'create_order':
                return 'We apologize, but there was an issue processing your order. Please try again or contact support.';
            
            case 'cancel_order':
                return 'We apologize, but there was an issue cancelling your order. Please contact support.';
            
            default:
                return 'We apologize, but there was an issue processing your request. Please try again or contact support.';
        }
    }

    /**
     * Aggiorna le metriche di successo
     */
    private function updateSuccessMetrics(Saga $saga): void
    {
        try {
            // Qui potresti aggiornare metriche di business intelligence
            // o inviare dati a sistemi di monitoring
            Log::info('Success metrics updated', [
                'saga_id' => $saga->id,
                'type' => $saga->type
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update success metrics', [
                'saga_id' => $saga->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Aggiorna le metriche di fallimento
     */
    private function updateFailureMetrics(Saga $saga): void
    {
        try {
            // Qui potresti aggiornare metriche di business intelligence
            // o inviare dati a sistemi di monitoring
            Log::info('Failure metrics updated', [
                'saga_id' => $saga->id,
                'type' => $saga->type
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update failure metrics', [
                'saga_id' => $saga->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Pulisce i dati temporanei
     */
    private function cleanupTemporaryData(Saga $saga): void
    {
        try {
            // Qui potresti pulire dati temporanei, cache, o altri stati
            // che non sono più necessari dopo il completamento della saga
            Log::info('Temporary data cleaned up', [
                'saga_id' => $saga->id
            ]);

        } catch (Exception $e) {
            Log::error('Failed to cleanup temporary data', [
                'saga_id' => $saga->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(Exception $exception): void
    {
        Log::error('Complete saga job failed', [
            'saga_id' => $this->sagaId,
            'final_status' => $this->finalStatus,
            'error' => $exception->getMessage()
        ]);

        $saga = Saga::find($this->sagaId);
        if ($saga) {
            $saga->status = 'failed';
            $saga->error = $exception->getMessage();
            $saga->save();
        }
    }
}
