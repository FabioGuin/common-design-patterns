<?php

namespace App\Repositories;

use App\Aggregates\Order;
use Illuminate\Support\Collection;

/**
 * Repository per la gestione degli Order Aggregate
 * 
 * Gestisce la persistenza e il recupero degli Order Aggregate
 * dal database.
 */
class OrderRepository
{
    private array $orders = [];
    private int $nextId = 1;

    /**
     * Salva un Order Aggregate
     */
    public function save(Order $order): void
    {
        $this->orders[$order->getId()] = $order;
    }

    /**
     * Trova un Order per ID
     */
    public function findById(string $id): ?Order
    {
        return $this->orders[$id] ?? null;
    }

    /**
     * Trova tutti gli Order per customer ID
     */
    public function findByCustomerId(string $customerId): Collection
    {
        return collect($this->orders)->filter(function (Order $order) use ($customerId) {
            return $order->getCustomerId() === $customerId;
        });
    }

    /**
     * Trova tutti gli Order per status
     */
    public function findByStatus(string $status): Collection
    {
        return collect($this->orders)->filter(function (Order $order) use ($status) {
            return $order->getStatus() === $status;
        });
    }

    /**
     * Trova tutti gli Order
     */
    public function findAll(): Collection
    {
        return collect($this->orders);
    }

    /**
     * Elimina un Order
     */
    public function delete(string $id): bool
    {
        if (isset($this->orders[$id])) {
            unset($this->orders[$id]);
            return true;
        }
        
        return false;
    }

    /**
     * Conta il numero di Order
     */
    public function count(): int
    {
        return count($this->orders);
    }

    /**
     * Genera un nuovo ID per Order
     */
    public function generateId(): string
    {
        return 'order-' . $this->nextId++;
    }

    /**
     * Verifica se un Order esiste
     */
    public function exists(string $id): bool
    {
        return isset($this->orders[$id]);
    }

    /**
     * Trova Order in un range di date
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): Collection
    {
        return collect($this->orders)->filter(function (Order $order) use ($startDate, $endDate) {
            $confirmedAt = $order->getConfirmedAt();
            if (!$confirmedAt) {
                return false;
            }
            
            return $confirmedAt >= $startDate && $confirmedAt <= $endDate;
        });
    }

    /**
     * Trova Order con totale superiore a un valore
     */
    public function findByMinTotal(float $minTotal): Collection
    {
        return collect($this->orders)->filter(function (Order $order) use ($minTotal) {
            return $order->getTotal() >= $minTotal;
        });
    }

    /**
     * Restituisce statistiche degli Order
     */
    public function getStatistics(): array
    {
        $orders = collect($this->orders);
        
        return [
            'total' => $orders->count(),
            'byStatus' => $orders->groupBy('status')->map->count(),
            'totalValue' => $orders->sum('total'),
            'averageValue' => $orders->avg('total'),
            'byCustomer' => $orders->groupBy('customerId')->map->count()
        ];
    }

    /**
     * Pulisce tutti gli Order (per testing)
     */
    public function clear(): void
    {
        $this->orders = [];
        $this->nextId = 1;
    }
}
