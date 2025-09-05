<?php

namespace App\Commands;

use App\Events\OrderCancelled;
use App\Services\EventStoreService;
use Illuminate\Support\Facades\Log;

class CancelOrderCommand
{
    protected $eventStore;

    public function __construct(EventStoreService $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * Esegue il command per cancellare un ordine
     */
    public function execute(string $orderId, string $reason = null)
    {
        try {
            // Verifica che l'ordine esista
            if (!$this->eventStore->aggregateExists($orderId)) {
                throw new \InvalidArgumentException("Ordine non trovato: {$orderId}");
            }

            // Verifica che l'ordine non sia già cancellato
            $currentStatus = $this->getCurrentOrderStatus($orderId);
            if ($currentStatus === 'cancelled') {
                throw new \InvalidArgumentException("Ordine già cancellato");
            }

            // Verifica che l'ordine non sia già spedito o consegnato
            if (in_array($currentStatus, ['shipped', 'delivered'])) {
                throw new \InvalidArgumentException("Impossibile cancellare ordine con status: {$currentStatus}");
            }

            // Crea l'evento di cancellazione
            $event = new OrderCancelled([
                'order_id' => $orderId,
                'cancelled_at' => now(),
                'reason' => $reason ?? 'Cancellato dall\'utente',
                'metadata' => [
                    'cancelled_by' => 'system',
                    'previous_status' => $currentStatus
                ]
            ]);

            // Salva l'evento nell'Event Store
            $this->eventStore->appendEvent($orderId, $event);

            Log::info("CQRS: Ordine cancellato", [
                'order_id' => $orderId,
                'event_type' => 'OrderCancelled',
                'reason' => $reason,
                'previous_status' => $currentStatus
            ]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'event_id' => $event->getEventId()
            ];

        } catch (\Exception $e) {
            Log::error("CQRS: Errore nella cancellazione dell'ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'reason' => $reason
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ottiene lo status attuale dell'ordine
     */
    private function getCurrentOrderStatus(string $orderId)
    {
        $events = $this->eventStore->getEvents($orderId);
        
        $status = 'pending'; // Status di default
        
        foreach ($events as $event) {
            switch ($event['event_type']) {
                case 'OrderCreated':
                    $status = 'pending';
                    break;
                case 'OrderUpdated':
                    if (isset($event['data']['updated_fields']['status'])) {
                        $status = $event['data']['updated_fields']['status'];
                    }
                    break;
                case 'OrderCancelled':
                    $status = 'cancelled';
                    break;
            }
        }
        
        return $status;
    }
}
