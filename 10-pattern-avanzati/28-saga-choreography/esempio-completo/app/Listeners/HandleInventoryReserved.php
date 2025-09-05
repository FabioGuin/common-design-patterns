<?php

namespace App\Listeners;

use App\Events\InventoryReserved;
use App\Services\OrderService;
use App\Events\OrderCreated;
use App\Events\InventoryReleaseRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Listener per gestire l'evento InventoryReserved
 * 
 * Questo listener crea un ordine quando l'inventario è stato riservato
 * e pubblica l'evento OrderCreated o InventoryReleaseRequested.
 */
class HandleInventoryReserved implements ShouldQueue
{
    use InteractsWithQueue;

    private OrderService $orderService;

    /**
     * Crea una nuova istanza del listener
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Gestisce l'evento
     */
    public function handle(InventoryReserved $event): void
    {
        try {
            Log::info('Handling InventoryReserved event', [
                'event_id' => $event->eventId,
                'reservation_id' => $event->reservationId,
                'product_id' => $event->productId,
                'quantity' => $event->quantity
            ]);

            // Simula la creazione dell'ordine
            $orderData = [
                'user_id' => $event->metadata['user_id'] ?? 1,
                'total' => $event->metadata['total'] ?? 29.99,
                'items' => [
                    [
                        'product_id' => $event->productId,
                        'quantity' => $event->quantity,
                        'price' => $event->metadata['price'] ?? 29.99
                    ]
                ]
            ];
            
            $order = $this->orderService->createOrder($orderData);
            
            // Pubblica l'evento OrderCreated
            $orderCreatedEvent = new OrderCreated(
                $order['id'],
                $order['user_id'],
                $order['total'],
                $order['status'],
                array_merge($event->metadata, [
                    'reservation_id' => $event->reservationId,
                    'product_id' => $event->productId,
                    'quantity' => $event->quantity
                ])
            );
            
            event($orderCreatedEvent);
            
            Log::info('OrderCreated event published', [
                'event_id' => $event->eventId,
                'order_id' => $order['id'],
                'user_id' => $order['user_id'],
                'total' => $order['total']
            ]);

        } catch (Exception $e) {
            Log::error('Failed to handle InventoryReserved event', [
                'event_id' => $event->eventId,
                'reservation_id' => $event->reservationId,
                'product_id' => $event->productId,
                'quantity' => $event->quantity,
                'error' => $e->getMessage()
            ]);

            // Pubblica l'evento di compensazione
            $inventoryReleaseRequestedEvent = new InventoryReleaseRequested(
                $event->reservationId,
                $event->productId,
                $event->quantity,
                $e->getMessage(),
                $event->metadata
            );
            
            event($inventoryReleaseRequestedEvent);
            
            // Rilancia l'eccezione per il retry
            throw $e;
        }
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(InventoryReserved $event, Exception $exception): void
    {
        Log::error('InventoryReserved event handling failed', [
            'event_id' => $event->eventId,
            'reservation_id' => $event->reservationId,
            'product_id' => $event->productId,
            'quantity' => $event->quantity,
            'error' => $exception->getMessage()
        ]);

        // Pubblica l'evento di compensazione
        $inventoryReleaseRequestedEvent = new InventoryReleaseRequested(
            $event->reservationId,
            $event->productId,
            $event->quantity,
            $exception->getMessage(),
            $event->metadata
        );
        
        event($inventoryReleaseRequestedEvent);
    }
}
