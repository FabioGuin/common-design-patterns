<?php

namespace App\Services;

class InventoryService
{
    private array $inventory = [];

    public function __construct()
    {
        $this->initializeInventory();
    }

    /**
     * Verifica la disponibilità di un prodotto
     */
    public function checkStock(string $productId): array
    {
        \Log::info('Checking stock for product', ['product_id' => $productId]);

        if (!isset($this->inventory[$productId])) {
            return [
                'available' => false,
                'quantity' => 0,
                'message' => 'Product not found',
            ];
        }

        $product = $this->inventory[$productId];
        $available = $product['quantity'] > 0;

        return [
            'available' => $available,
            'quantity' => $product['quantity'],
            'message' => $available ? 'Product available' : 'Product out of stock',
            'product' => $product,
        ];
    }

    /**
     * Riserva un prodotto
     */
    public function reserveItem(string $productId, int $quantity): array
    {
        \Log::info('Reserving item', ['product_id' => $productId, 'quantity' => $quantity]);

        $stockCheck = $this->checkStock($productId);

        if (!$stockCheck['available'] || $stockCheck['quantity'] < $quantity) {
            return [
                'success' => false,
                'message' => 'Insufficient stock',
                'available' => $stockCheck['quantity'],
                'requested' => $quantity,
            ];
        }

        $this->inventory[$productId]['quantity'] -= $quantity;
        $this->inventory[$productId]['reserved'] = ($this->inventory[$productId]['reserved'] ?? 0) + $quantity;

        return [
            'success' => true,
            'message' => 'Item reserved successfully',
            'reserved_quantity' => $quantity,
            'remaining_stock' => $this->inventory[$productId]['quantity'],
        ];
    }

    /**
     * Rilascia una riserva
     */
    public function releaseReservation(string $productId, int $quantity): array
    {
        \Log::info('Releasing reservation', ['product_id' => $productId, 'quantity' => $quantity]);

        if (!isset($this->inventory[$productId])) {
            return [
                'success' => false,
                'message' => 'Product not found',
            ];
        }

        $currentReserved = $this->inventory[$productId]['reserved'] ?? 0;
        $releaseQuantity = min($quantity, $currentReserved);

        $this->inventory[$productId]['quantity'] += $releaseQuantity;
        $this->inventory[$productId]['reserved'] = $currentReserved - $releaseQuantity;

        return [
            'success' => true,
            'message' => 'Reservation released successfully',
            'released_quantity' => $releaseQuantity,
            'remaining_reserved' => $this->inventory[$productId]['reserved'],
        ];
    }

    /**
     * Ottiene tutti i prodotti
     */
    public function getAllProducts(): array
    {
        return $this->inventory;
    }

    /**
     * Ottiene un prodotto specifico
     */
    public function getProduct(string $productId): ?array
    {
        return $this->inventory[$productId] ?? null;
    }

    /**
     * Inizializza l'inventario con prodotti di esempio
     */
    private function initializeInventory(): void
    {
        $this->inventory = [
            'PROD001' => [
                'id' => 'PROD001',
                'name' => 'Laptop Gaming',
                'price' => 1299.99,
                'quantity' => 10,
                'reserved' => 0,
                'category' => 'Electronics',
            ],
            'PROD002' => [
                'id' => 'PROD002',
                'name' => 'Smartphone',
                'price' => 699.99,
                'quantity' => 25,
                'reserved' => 0,
                'category' => 'Electronics',
            ],
            'PROD003' => [
                'id' => 'PROD003',
                'name' => 'Cuffie Wireless',
                'price' => 199.99,
                'quantity' => 50,
                'reserved' => 0,
                'category' => 'Accessories',
            ],
            'PROD004' => [
                'id' => 'PROD004',
                'name' => 'Tastiera Meccanica',
                'price' => 149.99,
                'quantity' => 30,
                'reserved' => 0,
                'category' => 'Accessories',
            ],
            'PROD005' => [
                'id' => 'PROD005',
                'name' => 'Monitor 4K',
                'price' => 599.99,
                'quantity' => 15,
                'reserved' => 0,
                'category' => 'Electronics',
            ],
        ];
    }
}
