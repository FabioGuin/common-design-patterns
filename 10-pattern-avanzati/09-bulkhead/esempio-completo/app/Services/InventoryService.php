<?php

namespace App\Services;

use App\Bulkhead\BulkheadManager;
use Illuminate\Support\Str;

class InventoryService
{
    public function __construct(
        private BulkheadManager $bulkheadManager
    ) {}

    public function checkAvailability(string $productId, int $quantity): array
    {
        return $this->bulkheadManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->performAvailabilityCheck($productId, $quantity);
        });
    }

    public function reserveInventory(string $productId, int $quantity): array
    {
        return $this->bulkheadManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->performReservation($productId, $quantity);
        });
    }

    public function updateStock(string $productId, int $quantity): array
    {
        return $this->bulkheadManager->execute('inventory_service', function () use ($productId, $quantity) {
            return $this->performStockUpdate($productId, $quantity);
        });
    }

    private function performAvailabilityCheck(string $productId, int $quantity): array
    {
        // Simula verifica inventario importante
        $this->simulateImportantOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 15) === 1) {
            throw new \Exception("Inventory check failed");
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

    private function performReservation(string $productId, int $quantity): array
    {
        // Simula riserva inventario importante
        $this->simulateImportantOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 18) === 1) {
            throw new \Exception("Inventory reservation failed");
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

    private function performStockUpdate(string $productId, int $quantity): array
    {
        // Simula aggiornamento stock importante
        $this->simulateImportantOperation();
        
        // Simula fallimento casuale per testing
        if (rand(1, 20) === 1) {
            throw new \Exception("Stock update failed");
        }

        return [
            'product_id' => $productId,
            'new_quantity' => $quantity,
            'updated_at' => now()->toISOString(),
            'priority' => 'medium',
        ];
    }

    private function simulateImportantOperation(): void
    {
        // Simula operazione importante con priorità media
        usleep(rand(100000, 400000)); // 100-400ms
    }

    public function getServiceStatus(): array
    {
        return $this->bulkheadManager->getBulkheadStatus('inventory_service') ?? [
            'service_name' => 'inventory_service',
            'status' => 'UNKNOWN',
            'message' => 'Bulkhead not initialized'
        ];
    }
}
