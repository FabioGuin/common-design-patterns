<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servizio per la gestione dell'inventario
 * 
 * Questo servizio gestisce le operazioni di inventario per il Saga Orchestration Pattern
 */
class InventoryService
{
    private string $id;
    private array $reservations;
    private int $totalOperations;
    private int $failedOperations;

    public function __construct()
    {
        $this->id = 'inventory-service-' . uniqid();
        $this->reservations = [];
        $this->totalOperations = 0;
        $this->failedOperations = 0;
        
        Log::info('InventoryService initialized', ['id' => $this->id]);
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Riserva l'inventario per un prodotto
     */
    public function reserveInventory(int $productId, int $quantity): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            // Simula la verifica dell'inventario disponibile
            $availableInventory = $this->getAvailableInventory($productId);
            
            if ($availableInventory < $quantity) {
                throw new Exception("Insufficient inventory for product {$productId}. Available: {$availableInventory}, Requested: {$quantity}");
            }
            
            // Simula la riserva dell'inventario
            $reservationId = 'reservation_' . uniqid();
            $this->reservations[$reservationId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'reserved_at' => now()->toISOString(),
                'expires_at' => now()->addMinutes(30)->toISOString()
            ];
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'reservation_id' => $reservationId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'reserved_at' => $this->reservations[$reservationId]['reserved_at'],
                'expires_at' => $this->reservations[$reservationId]['expires_at'],
                'duration' => $duration
            ];
            
            Log::info('Inventory reserved successfully', [
                'service' => $this->id,
                'reservation_id' => $reservationId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to reserve inventory', [
                'service' => $this->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Rilascia l'inventario riservato
     */
    public function releaseInventory(string $reservationId): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            if (!isset($this->reservations[$reservationId])) {
                throw new Exception("Reservation {$reservationId} not found");
            }
            
            $reservation = $this->reservations[$reservationId];
            unset($this->reservations[$reservationId]);
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'reservation_id' => $reservationId,
                'product_id' => $reservation['product_id'],
                'quantity' => $reservation['quantity'],
                'released_at' => now()->toISOString(),
                'duration' => $duration
            ];
            
            Log::info('Inventory released successfully', [
                'service' => $this->id,
                'reservation_id' => $reservationId,
                'product_id' => $reservation['product_id'],
                'quantity' => $reservation['quantity'],
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to release inventory', [
                'service' => $this->id,
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Conferma la riserva dell'inventario
     */
    public function confirmReservation(string $reservationId): array
    {
        $this->totalOperations++;
        $startTime = microtime(true);
        
        try {
            if (!isset($this->reservations[$reservationId])) {
                throw new Exception("Reservation {$reservationId} not found");
            }
            
            $reservation = $this->reservations[$reservationId];
            
            // Simula la conferma della riserva
            $this->reservations[$reservationId]['confirmed_at'] = now()->toISOString();
            $this->reservations[$reservationId]['status'] = 'confirmed';
            
            $duration = microtime(true) - $startTime;
            
            $result = [
                'reservation_id' => $reservationId,
                'product_id' => $reservation['product_id'],
                'quantity' => $reservation['quantity'],
                'confirmed_at' => $this->reservations[$reservationId]['confirmed_at'],
                'duration' => $duration
            ];
            
            Log::info('Inventory reservation confirmed', [
                'service' => $this->id,
                'reservation_id' => $reservationId,
                'product_id' => $reservation['product_id'],
                'quantity' => $reservation['quantity'],
                'duration' => $duration
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->failedOperations++;
            
            Log::error('Failed to confirm inventory reservation', [
                'service' => $this->id,
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Ottiene l'inventario disponibile per un prodotto
     */
    public function getAvailableInventory(int $productId): int
    {
        // Simula l'inventario disponibile
        $baseInventory = 100;
        $reservedQuantity = 0;
        
        // Calcola la quantità riservata
        foreach ($this->reservations as $reservation) {
            if ($reservation['product_id'] === $productId) {
                $reservedQuantity += $reservation['quantity'];
            }
        }
        
        return max(0, $baseInventory - $reservedQuantity);
    }

    /**
     * Ottiene le statistiche del servizio
     */
    public function getStats(): array
    {
        return [
            'id' => $this->id,
            'service' => 'InventoryService',
            'total_operations' => $this->totalOperations,
            'failed_operations' => $this->failedOperations,
            'success_rate' => $this->totalOperations > 0 
                ? round((($this->totalOperations - $this->failedOperations) / $this->totalOperations) * 100, 2)
                : 100,
            'active_reservations' => count($this->reservations),
            'reservations' => $this->reservations
        ];
    }

    /**
     * Pulisce le riserve scadute
     */
    public function cleanupExpiredReservations(): int
    {
        $now = now();
        $expiredCount = 0;
        
        foreach ($this->reservations as $reservationId => $reservation) {
            if (isset($reservation['expires_at']) && $now->isAfter($reservation['expires_at'])) {
                unset($this->reservations[$reservationId]);
                $expiredCount++;
            }
        }
        
        Log::info('Cleaned up expired reservations', [
            'service' => $this->id,
            'expired_count' => $expiredCount
        ]);
        
        return $expiredCount;
    }
}
