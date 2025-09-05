<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Events\PaymentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LogOrderActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $activityData = $this->buildActivityData($event);
            
            Log::info('Logging order activity', [
                'event_type' => get_class($event),
                'order_id' => $activityData['order_id'] ?? 'unknown'
            ]);

            // Salva l'attività nel database
            $this->saveActivity($activityData);

            Log::info('Order activity logged successfully', [
                'activity_id' => $activityData['id'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log order activity', [
                'event_type' => get_class($event),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Costruisce i dati dell'attività basati sull'evento
     */
    private function buildActivityData($event): array
    {
        $baseData = [
            'event_type' => get_class($event),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($event instanceof OrderCreated) {
            return array_merge($baseData, [
                'order_id' => $event->orderData['id'],
                'activity_type' => 'order_created',
                'description' => "Ordine creato per {$event->orderData['customer_name']}",
                'metadata' => json_encode([
                    'customer_name' => $event->orderData['customer_name'],
                    'amount' => $event->orderData['amount'],
                    'status' => $event->orderData['status']
                ])
            ]);
        }

        if ($event instanceof OrderUpdated) {
            return array_merge($baseData, [
                'order_id' => $event->orderData['id'],
                'activity_type' => 'order_updated',
                'description' => "Ordine aggiornato",
                'metadata' => json_encode([
                    'changes' => $event->changes,
                    'current_status' => $event->orderData['status']
                ])
            ]);
        }

        if ($event instanceof PaymentProcessed) {
            return array_merge($baseData, [
                'order_id' => $event->paymentData['order_id'],
                'activity_type' => 'payment_processed',
                'description' => "Pagamento processato per ordine #{$event->paymentData['order_id']}",
                'metadata' => json_encode([
                    'payment_id' => $event->paymentData['payment_id'],
                    'amount' => $event->paymentData['amount'],
                    'payment_method' => $event->paymentData['payment_method']
                ])
            ]);
        }

        return $baseData;
    }

    /**
     * Salva l'attività nel database
     */
    private function saveActivity(array $activityData): void
    {
        // In un sistema reale, useresti un modello Activity
        DB::table('order_activities')->insert($activityData);
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Order activity logging failed', [
            'event_type' => get_class($event),
            'error' => $exception->getMessage()
        ]);
    }
}
