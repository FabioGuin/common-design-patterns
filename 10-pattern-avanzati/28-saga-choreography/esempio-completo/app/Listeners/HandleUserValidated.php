<?php

namespace App\Listeners;

use App\Events\UserValidated;
use App\Services\InventoryService;
use App\Events\InventoryReserved;
use App\Events\UserValidationFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Listener per gestire l'evento UserValidated
 * 
 * Questo listener riserva l'inventario quando un utente è stato validato
 * e pubblica l'evento InventoryReserved o UserValidationFailed.
 */
class HandleUserValidated implements ShouldQueue
{
    use InteractsWithQueue;

    private InventoryService $inventoryService;

    /**
     * Crea una nuova istanza del listener
     */
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Gestisce l'evento
     */
    public function handle(UserValidated $event): void
    {
        try {
            Log::info('Handling UserValidated event', [
                'event_id' => $event->eventId,
                'user_id' => $event->userId,
                'user_name' => $event->userName
            ]);

            // Simula la riserva dell'inventario
            $productId = $event->metadata['product_id'] ?? 1;
            $quantity = $event->metadata['quantity'] ?? 1;
            
            $reservation = $this->inventoryService->reserveInventory($productId, $quantity);
            
            // Pubblica l'evento InventoryReserved
            $inventoryReservedEvent = new InventoryReserved(
                $reservation['reservation_id'],
                $reservation['product_id'],
                $reservation['quantity'],
                array_merge($event->metadata, [
                    'user_id' => $event->userId,
                    'user_name' => $event->userName,
                    'user_email' => $event->userEmail
                ])
            );
            
            event($inventoryReservedEvent);
            
            Log::info('InventoryReserved event published', [
                'event_id' => $event->eventId,
                'reservation_id' => $reservation['reservation_id'],
                'product_id' => $reservation['product_id'],
                'quantity' => $reservation['quantity']
            ]);

        } catch (Exception $e) {
            Log::error('Failed to handle UserValidated event', [
                'event_id' => $event->eventId,
                'user_id' => $event->userId,
                'error' => $e->getMessage()
            ]);

            // Pubblica l'evento di fallimento
            $userValidationFailedEvent = new UserValidationFailed(
                $event->userId,
                $event->userName,
                $event->userEmail,
                $e->getMessage(),
                $event->metadata
            );
            
            event($userValidationFailedEvent);
            
            // Rilancia l'eccezione per il retry
            throw $e;
        }
    }

    /**
     * Gestisce il fallimento del job
     */
    public function failed(UserValidated $event, Exception $exception): void
    {
        Log::error('UserValidated event handling failed', [
            'event_id' => $event->eventId,
            'user_id' => $event->userId,
            'error' => $exception->getMessage()
        ]);

        // Pubblica l'evento di fallimento
        $userValidationFailedEvent = new UserValidationFailed(
            $event->userId,
            $event->userName,
            $event->userEmail,
            $exception->getMessage(),
            $event->metadata
        );
        
        event($userValidationFailedEvent);
    }
}
