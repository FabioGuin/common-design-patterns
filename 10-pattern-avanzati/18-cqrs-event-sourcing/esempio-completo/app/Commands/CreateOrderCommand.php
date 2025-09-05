<?php

namespace App\Commands;

use App\Events\OrderCreated;
use App\Services\EventStoreService;
use Illuminate\Support\Facades\Log;

class CreateOrderCommand
{
    protected $eventStore;

    public function __construct(EventStoreService $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * Esegue il command per creare un nuovo ordine
     */
    public function execute(array $orderData)
    {
        try {
            // Valida i dati dell'ordine
            $this->validateOrderData($orderData);

            // Genera un ID univoco per l'ordine
            $orderId = $this->generateOrderId();

            // Crea l'evento
            $event = new OrderCreated([
                'order_id' => $orderId,
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'items' => $orderData['items'] ?? [],
                'total_amount' => $orderData['total_amount'] ?? 0,
                'status' => 'pending',
                'created_at' => now(),
                'metadata' => $orderData['metadata'] ?? []
            ]);

            // Salva l'evento nell'Event Store
            $this->eventStore->appendEvent($orderId, $event);

            Log::info("CQRS: Ordine creato", [
                'order_id' => $orderId,
                'event_type' => 'OrderCreated'
            ]);

            return [
                'success' => true,
                'order_id' => $orderId,
                'event_id' => $event->getEventId()
            ];

        } catch (\Exception $e) {
            Log::error("CQRS: Errore nella creazione dell'ordine", [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valida i dati dell'ordine
     */
    private function validateOrderData(array $orderData)
    {
        $required = ['customer_name', 'customer_email'];
        
        foreach ($required as $field) {
            if (!isset($orderData[$field]) || empty($orderData[$field])) {
                throw new \InvalidArgumentException("Campo obbligatorio mancante: {$field}");
            }
        }

        // Valida email
        if (!filter_var($orderData['customer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email non valida");
        }

        // Valida items se presenti
        if (isset($orderData['items']) && is_array($orderData['items'])) {
            foreach ($orderData['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                    throw new \InvalidArgumentException("Item non valido: mancano campi obbligatori");
                }
            }
        }
    }

    /**
     * Genera un ID univoco per l'ordine
     */
    private function generateOrderId()
    {
        return 'order_' . uniqid() . '_' . time();
    }
}
