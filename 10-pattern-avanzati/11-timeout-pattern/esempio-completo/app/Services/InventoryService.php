<?php

namespace App\Services;

use App\Timeout\TimeoutManager;
use Illuminate\Support\Str;

class InventoryService
{
    public function __construct(
        private TimeoutManager $timeoutManager
    ) {}

    public function checkAvailability(string $productId, int $quantity): array
    {
        return $this->timeoutManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->callExternalInventoryService($productId, $quantity);
        });
    }

    public function reserveInventory(string $productId, int $quantity): array
    {
        return $this->timeoutManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->callExternalReserveService($productId, $quantity);
        });
    }

    public function updateStock(string $productId, int $quantity): array
    {
        return $this->timeoutManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->callExternalUpdateService($productId, $quantity);
        });
    }

    private function callExternalInventoryService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula operazione lenta per testing
        if (rand(1, 6) === 1) {
            $this->simulateSlowOperation();
        }

        $availableStock = rand(0, 100);
        $isAvailable = $availableStock >= $quantity;

        return [
            'product_id' => $productId,
            'requested_quantity' => $quantity,
            'available_stock' => $availableStock,
            'is_available' => $isAvailable,
            'checked_at' => now()->toISOString(),
            'priority' => 'medium',
        ];
    }

    private function callExternalReserveService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula operazione lenta per testing
        if (rand(1, 7) === 1) {
            $this->simulateSlowOperation();
        }

        return [
            'reservation_id' => Str::uuid()->toString(),
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => 'reserved',
            'reserved_at' => now()->toISOString(),
            'expires_at' => now()->addMinutes(30)->toISOString(),
            'priority' => 'medium',
        ];
    }

    private function callExternalUpdateService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();
        
        // Simula operazione lenta per testing
        if (rand(1, 8) === 1) {
            $this->simulateSlowOperation();
        }

        return [
            'product_id' => $productId,
            'new_quantity' => $quantity,
            'updated_at' => now()->toISOString(),
            'priority' => 'medium',
        ];
    }

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete normale
        usleep(rand(50000, 300000)); // 50-300ms
    }

    private function simulateSlowOperation(): void
    {
        // Simula operazione lenta che causerà timeout
        usleep(rand(15000000, 25000000)); // 15-25 secondi
    }

    public function getServiceStatus(): array
    {
        return $this->timeoutManager->getTimeoutStatus('inventory_service') ?? [
            'service_name' => 'inventory_service',
            'status' => 'UNKNOWN',
            'message' => 'Timeout not initialized'
        ];
    }
}
