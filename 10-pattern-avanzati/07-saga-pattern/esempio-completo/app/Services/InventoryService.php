<?php

namespace App\Services;

use App\Models\InventoryReservation;
use Illuminate\Support\Str;

class InventoryService
{
    public function reserveInventory(array $orderData): array
    {
        $productId = $orderData['product_id'];
        $quantity = $orderData['quantity'];

        // Simula verifica disponibilità
        $availableStock = $this->getAvailableStock($productId);
        
        if ($availableStock < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$availableStock}, Required: {$quantity}");
        }

        // Crea riserva
        $reservation = InventoryReservation::create([
            'reservation_id' => Str::uuid()->toString(),
            'product_id' => $productId,
            'quantity' => $quantity,
            'order_id' => $orderData['order_id'],
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(30),
        ]);

        // Simula aggiornamento stock
        $this->updateStock($productId, -$quantity);

        return [
            'reservation_id' => $reservation->reservation_id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'expires_at' => $reservation->expires_at
        ];
    }

    public function releaseInventory(string $reservationId): array
    {
        $reservation = InventoryReservation::where('reservation_id', $reservationId)->first();
        
        if (!$reservation) {
            throw new \Exception("Reservation not found: {$reservationId}");
        }

        if ($reservation->status !== 'reserved') {
            throw new \Exception("Reservation already released: {$reservationId}");
        }

        // Rilascia riserva
        $reservation->update(['status' => 'released']);

        // Ripristina stock
        $this->updateStock($reservation->product_id, $reservation->quantity);

        return [
            'reservation_id' => $reservationId,
            'status' => 'released',
            'quantity_returned' => $reservation->quantity
        ];
    }

    public function getAvailableStock(string $productId): int
    {
        // Simula query al database
        return rand(0, 100);
    }

    private function updateStock(string $productId, int $quantityChange): void
    {
        // Simula aggiornamento stock nel database
        // In un'implementazione reale, aggiorneresti la tabella products
    }

    public function getReservation(string $reservationId): ?InventoryReservation
    {
        return InventoryReservation::where('reservation_id', $reservationId)->first();
    }

    public function getAllReservations(): array
    {
        return InventoryReservation::orderBy('created_at', 'desc')->get()->toArray();
    }
}
