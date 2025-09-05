<?php

namespace App\Services;

use App\Throttling\ThrottlingManager;
use Illuminate\Support\Str;

class InventoryService
{
    public function __construct(
        private ThrottlingManager $throttlingManager
    ) {}

    public function checkAvailability(string $productId, int $quantity, string $userId): array
    {
        return $this->throttlingManager->execute('inventory_service', $userId, function () use ($productId, $quantity) {
            return $this->callExternalInventoryService($productId, $quantity);
        }, 'api/inventory');
    }

    public function reserveInventory(string $productId, int $quantity, string $userId): array
    {
        return $this->throttlingManager->execute('inventory_service', $userId, function () use ($productId, $quantity) {
            return $this->callExternalReserveService($productId, $quantity);
        }, 'api/reserve');
    }

    public function updateStock(string $productId, int $quantity, string $userId): array
    {
        return $this->throttlingManager->execute('inventory_service', $userId, function () use ($productId, $quantity) {
            return $this->callExternalUpdateService($productId, $quantity);
        }, 'api/update');
    }

    private function callExternalInventoryService(string $productId, int $quantity): array
    {
        // Simula chiamata a servizio esterno
        $this->simulateExternalCall();

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

        return [
            'product_id' => $productId,
            'new_quantity' => $quantity,
            'updated_at' => now()->toISOString(),
            'priority' => 'medium',
        ];
    }

    private function simulateExternalCall(): void
    {
        // Simula latenza di rete
        usleep(rand(50000, 300000)); // 50-300ms
    }

    public function getServiceStatus(string $userId): array
    {
        return $this->throttlingManager->getThrottlingStatus('inventory_service', $userId, 'api/inventory') ?? [
            'service_name' => 'inventory_service',
            'status' => 'UNKNOWN',
            'message' => 'Throttling not initialized'
        ];
    }
}
