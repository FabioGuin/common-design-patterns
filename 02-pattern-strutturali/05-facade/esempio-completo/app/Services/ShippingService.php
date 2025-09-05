<?php

namespace App\Services;

class ShippingService
{
    private array $shipments = [];
    private float $shippingCost;

    public function __construct()
    {
        $this->shippingCost = config('ecommerce.shipping_cost', 5.99);
    }

    /**
     * Crea una spedizione
     */
    public function createShipment(array $orderData): array
    {
        \Log::info('Creating shipment', $orderData);

        $shipmentId = 'SHIP_' . uniqid();
        $trackingNumber = 'TRK' . strtoupper(substr(uniqid(), -8));

        $shipment = [
            'id' => $shipmentId,
            'order_id' => $orderData['order_id'],
            'tracking_number' => $trackingNumber,
            'status' => 'created',
            'shipping_address' => $orderData['shipping_address'],
            'shipping_cost' => $this->shippingCost,
            'estimated_delivery' => now()->addDays(3)->toISOString(),
            'created_at' => now()->toISOString(),
        ];

        $this->shipments[$shipmentId] = $shipment;

        return [
            'success' => true,
            'shipment_id' => $shipmentId,
            'tracking_number' => $trackingNumber,
            'message' => 'Shipment created successfully',
            'shipping_cost' => $this->shippingCost,
            'estimated_delivery' => $shipment['estimated_delivery'],
        ];
    }

    /**
     * Aggiorna lo stato di una spedizione
     */
    public function updateShipmentStatus(string $shipmentId, string $status): array
    {
        \Log::info('Updating shipment status', ['shipment_id' => $shipmentId, 'status' => $status]);

        if (!isset($this->shipments[$shipmentId])) {
            return [
                'success' => false,
                'message' => 'Shipment not found',
            ];
        }

        $this->shipments[$shipmentId]['status'] = $status;
        $this->shipments[$shipmentId]['updated_at'] = now()->toISOString();

        return [
            'success' => true,
            'message' => 'Shipment status updated successfully',
            'status' => $status,
        ];
    }

    /**
     * Traccia una spedizione
     */
    public function trackShipment(string $trackingNumber): array
    {
        \Log::info('Tracking shipment', ['tracking_number' => $trackingNumber]);

        $shipment = $this->findShipmentByTrackingNumber($trackingNumber);

        if (!$shipment) {
            return [
                'success' => false,
                'message' => 'Shipment not found',
            ];
        }

        return [
            'success' => true,
            'shipment' => $shipment,
            'message' => 'Shipment found',
        ];
    }

    /**
     * Ottiene una spedizione per ID
     */
    public function getShipment(string $shipmentId): ?array
    {
        return $this->shipments[$shipmentId] ?? null;
    }

    /**
     * Ottiene tutte le spedizioni
     */
    public function getAllShipments(): array
    {
        return $this->shipments;
    }

    /**
     * Calcola il costo di spedizione
     */
    public function calculateShippingCost(array $items, string $address): array
    {
        $baseCost = $this->shippingCost;
        $itemCount = count($items);
        $totalWeight = array_sum(array_column($items, 'weight'));

        // Calcola costi aggiuntivi basati su peso e quantità
        $weightMultiplier = $totalWeight > 5 ? 1.5 : 1.0;
        $quantityMultiplier = $itemCount > 3 ? 1.2 : 1.0;

        $totalCost = $baseCost * $weightMultiplier * $quantityMultiplier;

        return [
            'base_cost' => $baseCost,
            'weight_multiplier' => $weightMultiplier,
            'quantity_multiplier' => $quantityMultiplier,
            'total_cost' => $totalCost,
            'items_count' => $itemCount,
            'total_weight' => $totalWeight,
        ];
    }

    /**
     * Trova una spedizione per numero di tracking
     */
    private function findShipmentByTrackingNumber(string $trackingNumber): ?array
    {
        foreach ($this->shipments as $shipment) {
            if ($shipment['tracking_number'] === $trackingNumber) {
                return $shipment;
            }
        }

        return null;
    }

    /**
     * Ottiene le opzioni di spedizione disponibili
     */
    public function getShippingOptions(): array
    {
        return [
            'standard' => [
                'name' => 'Standard Shipping',
                'cost' => $this->shippingCost,
                'delivery_days' => 3,
                'description' => 'Delivery within 3 business days',
            ],
            'express' => [
                'name' => 'Express Shipping',
                'cost' => $this->shippingCost * 2,
                'delivery_days' => 1,
                'description' => 'Next business day delivery',
            ],
            'overnight' => [
                'name' => 'Overnight Shipping',
                'cost' => $this->shippingCost * 3,
                'delivery_days' => 0,
                'description' => 'Same day delivery',
            ],
        ];
    }
}
