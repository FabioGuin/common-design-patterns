<?php

namespace App\Fallbacks;

use Illuminate\Support\Str;

class InventoryFallback
{
    public function checkAvailability(string $productId, int $quantity): array
    {
        // Simula verifica offline basata su cache locale
        usleep(50000); // 50ms

        // Stima disponibilità basata su dati storici
        $estimatedStock = $this->getEstimatedStock($productId);
        $isAvailable = $estimatedStock >= $quantity;

        return [
            'product_id' => $productId,
            'requested_quantity' => $quantity,
            'estimated_stock' => $estimatedStock,
            'is_available' => $isAvailable,
            'checked_at' => now()->toISOString(),
            'fallback_reason' => 'Inventory service unavailable - using estimated data',
            'confidence_level' => 'medium',
        ];
    }

    public function reserveInventory(string $productId, int $quantity): array
    {
        // Simula riserva offline
        usleep(50000); // 50ms

        return [
            'reservation_id' => Str::uuid()->toString(),
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => 'pending_offline',
            'reserved_at' => now()->toISOString(),
            'expires_at' => now()->addMinutes(15)->toISOString(), // Tempo ridotto per fallback
            'fallback_reason' => 'Inventory service unavailable - reserved offline',
            'requires_manual_confirmation' => true,
        ];
    }

    public function releaseInventory(string $reservationId): array
    {
        // Simula rilascio offline
        usleep(50000); // 50ms

        return [
            'reservation_id' => $reservationId,
            'status' => 'pending_offline',
            'released_at' => now()->toISOString(),
            'fallback_reason' => 'Inventory service unavailable - released offline',
            'requires_manual_confirmation' => true,
        ];
    }

    private function getEstimatedStock(string $productId): int
    {
        // Simula stima basata su dati storici
        // In un'implementazione reale, useresti cache o dati storici
        return rand(0, 50);
    }
}
