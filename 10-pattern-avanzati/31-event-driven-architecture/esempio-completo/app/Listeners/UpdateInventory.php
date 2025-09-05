<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateInventory implements ShouldQueue
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
            if ($event instanceof OrderCreated) {
                $this->handleOrderCreated($event);
            } elseif ($event instanceof OrderUpdated) {
                $this->handleOrderUpdated($event);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update inventory', [
                'event_type' => get_class($event),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Gestisce la creazione di un ordine
     */
    private function handleOrderCreated(OrderCreated $event): void
    {
        Log::info('Updating inventory for new order', [
            'order_id' => $event->orderData['id'],
            'amount' => $event->orderData['amount']
        ]);

        // Simula l'aggiornamento dell'inventario
        $this->decreaseInventory($event->orderData);

        Log::info('Inventory updated for new order', [
            'order_id' => $event->orderData['id']
        ]);
    }

    /**
     * Gestisce l'aggiornamento di un ordine
     */
    private function handleOrderUpdated(OrderUpdated $event): void
    {
        Log::info('Updating inventory for order update', [
            'order_id' => $event->orderData['id'],
            'changes' => $event->changes
        ]);

        // Se l'ordine è stato cancellato, ripristina l'inventario
        if (isset($event->changes['status']) && $event->changes['status'] === 'cancelled') {
            $this->restoreInventory($event->orderData);
        }

        Log::info('Inventory updated for order update', [
            'order_id' => $event->orderData['id']
        ]);
    }

    /**
     * Diminuisce l'inventario
     */
    private function decreaseInventory(array $orderData): void
    {
        // In un sistema reale, qui aggiorneresti la tabella inventory
        Log::info('Decreasing inventory', [
            'order_id' => $orderData['id'],
            'amount' => $orderData['amount']
        ]);

        // Simula l'aggiornamento dell'inventario
        DB::table('inventory_logs')->insert([
            'order_id' => $orderData['id'],
            'action' => 'decrease',
            'amount' => $orderData['amount'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Simula delay di aggiornamento
        usleep(rand(50000, 150000)); // 50-150ms
    }

    /**
     * Ripristina l'inventario
     */
    private function restoreInventory(array $orderData): void
    {
        // In un sistema reale, qui ripristineresti l'inventario
        Log::info('Restoring inventory', [
            'order_id' => $orderData['id'],
            'amount' => $orderData['amount']
        ]);

        // Simula il ripristino dell'inventario
        DB::table('inventory_logs')->insert([
            'order_id' => $orderData['id'],
            'action' => 'restore',
            'amount' => $orderData['amount'],
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Simula delay di ripristino
        usleep(rand(50000, 150000)); // 50-150ms
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Inventory update failed', [
            'event_type' => get_class($event),
            'error' => $exception->getMessage()
        ]);
    }
}
