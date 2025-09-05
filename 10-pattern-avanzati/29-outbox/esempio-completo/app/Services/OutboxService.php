<?php

namespace App\Services;

use App\Models\OutboxEvent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutboxService
{
    /**
     * Crea un ordine e inserisce l'evento nell'outbox in una transazione atomica
     */
    public function createOrderWithEvent(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            // Crea l'ordine
            $order = Order::create($orderData);
            
            // Inserisce l'evento nell'outbox nella stessa transazione
            $this->addEventToOutbox('OrderCreated', [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'amount' => $order->amount,
                'created_at' => $order->created_at->toISOString()
            ], $order->id);
            
            Log::info('Order created with outbox event', [
                'order_id' => $order->id,
                'event_type' => 'OrderCreated'
            ]);
            
            return $order;
        });
    }

    /**
     * Aggiorna un ordine e inserisce l'evento nell'outbox
     */
    public function updateOrderWithEvent(int $orderId, array $updateData): Order
    {
        return DB::transaction(function () use ($orderId, $updateData) {
            $order = Order::findOrFail($orderId);
            $order->update($updateData);
            
            $this->addEventToOutbox('OrderUpdated', [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'amount' => $order->amount,
                'updated_at' => $order->updated_at->toISOString()
            ], $order->id);
            
            Log::info('Order updated with outbox event', [
                'order_id' => $order->id,
                'event_type' => 'OrderUpdated'
            ]);
            
            return $order;
        });
    }

    /**
     * Cancella un ordine e inserisce l'evento nell'outbox
     */
    public function deleteOrderWithEvent(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::findOrFail($orderId);
            $orderData = $order->toArray();
            
            $this->addEventToOutbox('OrderDeleted', [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'amount' => $order->amount,
                'deleted_at' => now()->toISOString()
            ], $order->id);
            
            $deleted = $order->delete();
            
            Log::info('Order deleted with outbox event', [
                'order_id' => $orderId,
                'event_type' => 'OrderDeleted'
            ]);
            
            return $deleted;
        });
    }

    /**
     * Aggiunge un evento all'outbox
     */
    public function addEventToOutbox(string $eventType, array $eventData, ?int $aggregateId = null): OutboxEvent
    {
        return OutboxEvent::create([
            'event_type' => $eventType,
            'event_data' => $eventData,
            'aggregate_id' => $aggregateId,
            'status' => 'pending',
            'retry_count' => 0,
            'scheduled_at' => now()
        ]);
    }

    /**
     * Recupera eventi pendenti dall'outbox
     */
    public function getPendingEvents(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return OutboxEvent::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Marca un evento come in processing
     */
    public function markEventAsProcessing(OutboxEvent $event): bool
    {
        return $event->update([
            'status' => 'processing',
            'processing_started_at' => now()
        ]);
    }

    /**
     * Marca un evento come pubblicato con successo
     */
    public function markEventAsPublished(OutboxEvent $event): bool
    {
        return $event->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    /**
     * Marca un evento come fallito e programma un retry
     */
    public function markEventAsFailed(OutboxEvent $event, string $error = null): bool
    {
        $retryCount = $event->retry_count + 1;
        $maxRetries = config('outbox.max_retries', 3);
        
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
     * Pulisce eventi pubblicati più vecchi di X giorni
     */
    public function cleanupPublishedEvents(int $daysOld = 7): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return OutboxEvent::where('status', 'published')
            ->where('published_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Ottiene statistiche dell'outbox
     */
    public function getOutboxStats(): array
    {
        return [
            'pending' => OutboxEvent::where('status', 'pending')->count(),
            'processing' => OutboxEvent::where('status', 'processing')->count(),
            'published' => OutboxEvent::where('status', 'published')->count(),
            'failed' => OutboxEvent::where('status', 'failed')->count(),
            'total' => OutboxEvent::count()
        ];
    }
}
