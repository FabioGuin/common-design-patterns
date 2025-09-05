<?php

namespace App\Commands;

use App\Events\OrderUpdated;
use App\Services\EventStoreService;
use Illuminate\Support\Facades\Log;

class UpdateOrderCommand
{
    protected $eventStore;

    public function __construct(EventStoreService $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * Esegue il command per aggiornare un ordine esistente
     */
    public function execute(string $orderId, array $updateData)
    {
        try {
            // Verifica che l'ordine esista
            if (!$this->eventStore->aggregateExists($orderId)) {
                throw new \InvalidArgumentException("Ordine non trovato: {$orderId}");
            }

            // Valida i dati di aggiornamento
            $this->validateUpdateData($updateData);

            // Crea l'evento di aggiornamento
            $event = new OrderUpdated([
                'order_id' => $orderId,
                'updated_fields' => $updateData,
                'updated_at' => now(),
                'metadata' => $updateData['metadata'] ?? []
            ]);

            // Salva l'evento nell'Event Store
            $this->eventStore->appendEvent($orderId, $event);

            Log::info("CQRS: Ordine aggiornato", [
                'order_id' => $orderId,
                'event_type' => 'OrderUpdated',
                'updated_fields' => array_keys($updateData)
            ]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'event_id' => $event->getEventId()
            ];

        } catch (\Exception $e) {
            Log::error("CQRS: Errore nell'aggiornamento dell'ordine", [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'update_data' => $updateData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida i dati di aggiornamento
     */
    private function validateUpdateData(array $updateData)
    {
        // Rimuovi metadata dalla validazione
        unset($updateData['metadata']);

        if (empty($updateData)) {
            throw new \InvalidArgumentException("Nessun dato da aggiornare");
        }

        // Valida email se presente
        if (isset($updateData['customer_email'])) {
            if (!filter_var($updateData['customer_email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Email non valida");
            }
        }

        // Valida status se presente
        if (isset($updateData['status'])) {
            $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($updateData['status'], $validStatuses)) {
                throw new \InvalidArgumentException("Status non valido: {$updateData['status']}");
            }
        }

        // Valida total_amount se presente
        if (isset($updateData['total_amount'])) {
            if (!is_numeric($updateData['total_amount']) || $updateData['total_amount'] < 0) {
                throw new \InvalidArgumentException("Total amount non valido");
            }
        }

        // Valida items se presenti
        if (isset($updateData['items']) && is_array($updateData['items'])) {
            foreach ($updateData['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                    throw new \InvalidArgumentException("Item non valido: mancano campi obbligatori");
                }
            }
        }
    }
}
