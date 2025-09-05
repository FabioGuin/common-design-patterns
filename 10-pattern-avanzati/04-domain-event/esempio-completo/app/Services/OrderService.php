<?php

namespace App\Services;

/**
 * Servizio per gestire gli ordini
 * 
 * Gestisce lo status degli ordini, aggiornamenti
 * e operazioni correlate.
 */
class OrderService
{
    private array $orders = [];

    /**
     * Aggiorna lo status di un ordine
     */
    public function updateStatus(string $orderId, string $status, array $metadata = []): void
    {
        if (!isset($this->orders[$orderId])) {
            $this->orders[$orderId] = [
                'id' => $orderId,
                'status' => $status,
                'metadata' => $metadata,
                'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updatedAt' => (new \DateTime())->format('Y-m-d H:i:s')
            ];
        } else {
            $this->orders[$orderId]['status'] = $status;
            $this->orders[$orderId]['metadata'] = array_merge($this->orders[$orderId]['metadata'], $metadata);
            $this->orders[$orderId]['updatedAt'] = (new \DateTime())->format('Y-m-d H:i:s');
        }
    }

    /**
     * Restituisce un ordine per ID
     */
    public function getOrder(string $orderId): ?array
    {
        return $this->orders[$orderId] ?? null;
    }

    /**
     * Restituisce tutti gli ordini
     */
    public function getAllOrders(): array
    {
        return $this->orders;
    }

    /**
     * Restituisce gli ordini per status
     */
    public function getOrdersByStatus(string $status): array
    {
        return array_filter($this->orders, function ($order) use ($status) {
            return $order['status'] === $status;
        });
    }

    /**
     * Restituisce le statistiche degli ordini
     */
    public function getStatistics(): array
    {
        $totalOrders = count($this->orders);
        $statusCounts = [];
        
        foreach ($this->orders as $order) {
            $status = $order['status'];
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        return [
            'totalOrders' => $totalOrders,
            'byStatus' => $statusCounts
        ];
    }

    /**
     * Pulisce tutti gli ordini
     */
    public function clear(): void
    {
        $this->orders = [];
    }
}
