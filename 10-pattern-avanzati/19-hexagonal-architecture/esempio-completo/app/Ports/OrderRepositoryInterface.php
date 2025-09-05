<?php

namespace App\Ports;

use App\Domain\Order;

interface OrderRepositoryInterface
{
    /**
     * Salva un ordine
     */
    public function save(Order $order): Order;

    /**
     * Trova un ordine per ID
     */
    public function findById(string $id): ?Order;

    /**
     * Trova tutti gli ordini
     */
    public function findAll(int $limit = 100, int $offset = 0): array;

    /**
     * Trova ordini per email cliente
     */
    public function findByCustomerEmail(string $customerEmail): array;

    /**
     * Trova ordini per status
     */
    public function findByStatus(string $status): array;

    /**
     * Trova ordini in un range di date
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array;

    /**
     * Conta il numero totale di ordini
     */
    public function count(): int;

    /**
     * Conta ordini per status
     */
    public function countByStatus(string $status): int;

    /**
     * Elimina un ordine
     */
    public function delete(string $id): bool;

    /**
     * Verifica se un ordine esiste
     */
    public function exists(string $id): bool;
}
