<?php

namespace App\Services;

use App\CircuitBreaker\CircuitBreakerManager;
use App\Fallbacks\InventoryFallback;

class InventoryService
{
    public function __construct(
        private CircuitBreakerManager $circuitBreakerManager,
        private InventoryFallback $inventoryFallback
    ) {}

    public function checkAvailability(string $productId, int $quantity): array
    {
        return $this->circuitBreakerManager->call(
            'inventory_service',
            function () use ($productId, $quantity) {
                return $this->callExternalInventoryService($productId, $quantity);
            },
            function () use ($productId, $quantity) {
                return $this->inventoryFallback->checkAvailability($productId, $quantity);
            }
        );
    }

    public function reserveInventory(string $productId, int $quantity): array
    {
        return $this->circuitBreakerManager->call(
            'inventory_service',
            function () use ($productId, $quantity) {
                return $this->callExternalReserveService($productId, $quantity);
            },
            function () use ($productId, $quantity) {
                return $this->inventoryFallback->reserveInventory($productId, $quantity);
            }
        );
    }

    public function releaseInventory(string $reservationId): array
    {
        return $this->circuitBreakerManager->call(
            'inventory_service',
            function () use ($reservationId) {
                return $this->callExternalReleaseService($reservationId);
            },
            function () use ($reservationId) {
                return $this->inventoryFallback->releaseInventory($reservationId);
            }
        );
    }

    private function callExternalInventoryService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 12) === 1) {
            throw new \Exception("Inventory service temporarily unavailable");
        }

        $availableStock = rand(0, 100);
        $isAvailable = $availableStock >= $quantity;

        return [
            'product_id' => $productId,
            'requested_quantity' => $quantity,
            'available_stock' => $availableStock,
            'is_available' => $isAvailable,
            'checked_at' => now()->toISOString(),
        ];
    }

    private function callExternalReserveService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 12) === 1) {
            throw new \Exception("Inventory reservation service temporarily unavailable");
        }

        return [
            'reservation_id' => \Illuminate\Support\Str::uuid()->toString(),
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => 'reserved',
            'reserved_at' => now()->toISOString(),
            'expires_at' => now()->addMinutes(30)->toISOString(),
        ];
    }

    private function callExternalReleaseService(string $reservationId): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula fallimento casuale per testing
        if (rand(1, 12) === 1) {
            throw new \Exception("Inventory release service temporarily unavailable");
        }

        return [
            'reservation_id' => $reservationId,
            'status' => 'released',
            'released_at' => now()->toISOString(),
        ];
    }

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete
        usleep(rand(50000, 300000)); // 50-300ms
    }

    public function getServiceStatus(): array
    {
        return $this->circuitBreakerManager->getCircuitBreakerState('inventory_service') ?? [
            'service_name' => 'inventory_service',
            'state' => 'UNKNOWN',
            'message' => 'Circuit breaker not initialized'
        ];
    }
}
