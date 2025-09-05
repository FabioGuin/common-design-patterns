<?php

namespace App\Services;

/**
 * Servizio per gestire l'inventario
 * 
 * Gestisce lo stock dei prodotti, aggiornamenti
 * e notifiche per stock basso.
 */
class InventoryService
{
    private array $inventory = [];
    private array $stockMovements = [];

    public function __construct()
    {
        // Inizializza con alcuni prodotti di esempio
        $this->inventory = [
            'PROD-001' => ['name' => 'Product 1', 'stock' => 100, 'minStock' => 10],
            'PROD-002' => ['name' => 'Product 2', 'stock' => 50, 'minStock' => 5],
            'PROD-003' => ['name' => 'Product 3', 'stock' => 25, 'minStock' => 3],
        ];
    }

    /**
     * Diminuisce lo stock di un prodotto
     */
    public function decreaseStock(string $productId, int $quantity): void
    {
        if (!isset($this->inventory[$productId])) {
            throw new \InvalidArgumentException("Product {$productId} not found");
        }

        if ($this->inventory[$productId]['stock'] < $quantity) {
            throw new \InvalidArgumentException("Insufficient stock for product {$productId}");
        }

        $this->inventory[$productId]['stock'] -= $quantity;
        
        $this->logStockMovement($productId, -$quantity, 'decrease', 'order_confirmed');
        
        // Verifica se lo stock è basso
        if ($this->inventory[$productId]['stock'] <= $this->inventory[$productId]['minStock']) {
            $this->handleLowStock($productId);
        }
    }

    /**
     * Aumenta lo stock di un prodotto
     */
    public function increaseStock(string $productId, int $quantity): void
    {
        if (!isset($this->inventory[$productId])) {
            throw new \InvalidArgumentException("Product {$productId} not found");
        }

        $this->inventory[$productId]['stock'] += $quantity;
        
        $this->logStockMovement($productId, $quantity, 'increase', 'order_cancelled');
    }

    /**
     * Restituisce lo stock di un prodotto
     */
    public function getStock(string $productId): int
    {
        return $this->inventory[$productId]['stock'] ?? 0;
    }

    /**
     * Restituisce tutti i prodotti
     */
    public function getAllProducts(): array
    {
        return $this->inventory;
    }

    /**
     * Restituisce i prodotti con stock basso
     */
    public function getLowStockProducts(): array
    {
        return array_filter($this->inventory, function ($product) {
            return $product['stock'] <= $product['minStock'];
        });
    }

    /**
     * Restituisce i movimenti di stock
     */
    public function getStockMovements(): array
    {
        return $this->stockMovements;
    }

    /**
     * Restituisce i movimenti per un prodotto
     */
    public function getStockMovementsForProduct(string $productId): array
    {
        return array_filter($this->stockMovements, function ($movement) use ($productId) {
            return $movement['productId'] === $productId;
        });
    }

    /**
     * Restituisce le statistiche dell'inventario
     */
    public function getStatistics(): array
    {
        $totalProducts = count($this->inventory);
        $totalStock = array_sum(array_column($this->inventory, 'stock'));
        $lowStockCount = count($this->getLowStockProducts());
        $totalMovements = count($this->stockMovements);

        return [
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStockCount' => $lowStockCount,
            'totalMovements' => $totalMovements,
            'averageStock' => $totalProducts > 0 ? round($totalStock / $totalProducts, 2) : 0
        ];
    }

    /**
     * Pulisce i movimenti di stock
     */
    public function clearStockMovements(): void
    {
        $this->stockMovements = [];
    }

    /**
     * Gestisce lo stock basso
     */
    private function handleLowStock(string $productId): void
    {
        // In un'applicazione reale, qui invieresti una notifica
        // o creeresti un task per riordinare il prodotto
        error_log("Low stock alert for product {$productId}");
    }

    /**
     * Logga un movimento di stock
     */
    private function logStockMovement(
        string $productId,
        int $quantity,
        string $type,
        string $reason
    ): void {
        $this->stockMovements[] = [
            'productId' => $productId,
            'quantity' => $quantity,
            'type' => $type,
            'reason' => $reason,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];
    }
}
