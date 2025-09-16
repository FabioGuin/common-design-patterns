<?php

namespace App\Services;

use App\ValueObjects\Address;
use App\ValueObjects\Email;
use App\ValueObjects\Price;

class OrderService
{
    public function createOrder(
        string $productName,
        Email $customerEmail,
        Price $price,
        Address $shippingAddress
    ): array {
        // Simula calcoli di business con Value Object
        $taxRate = 0.22; // 22% IVA
        $taxAmount = $price->multiply($taxRate);
        $totalPrice = $price->add($taxAmount);
        
        // Simula logica di spedizione
        $shippingCost = $this->calculateShippingCost($shippingAddress);
        $finalPrice = $totalPrice->add($shippingCost);

        return [
            'order_id' => 'ORD-' . uniqid(),
            'product_name' => $productName,
            'customer_email' => $customerEmail->getValue(),
            'base_price' => $price->__toString(),
            'tax_amount' => $taxAmount->__toString(),
            'shipping_cost' => $shippingCost->__toString(),
            'total_price' => $finalPrice->__toString(),
            'shipping_address' => $shippingAddress->getFullAddress(),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function validateOrder(Email $email, Price $price, Address $address): array
    {
        $errors = [];

        // Validazione business con Value Object
        if ($price->isZero()) {
            $errors[] = 'Il prezzo non può essere zero';
        }

        if ($price->isGreaterThan(Price::fromEuros(10000))) {
            $errors[] = 'Ordini superiori a €10.000 richiedono approvazione';
        }

        if (!$address->isInCountry('IT') && $price->isGreaterThan(Price::fromEuros(1000))) {
            $errors[] = 'Ordini superiori a €1.000 fuori dall\'Italia richiedono documentazione aggiuntiva';
        }

        return $errors;
    }

    private function calculateShippingCost(Address $address): Price
    {
        // Logica di calcolo spedizione basata su Value Object
        if ($address->isInCountry('IT')) {
            return Price::fromEuros(5.99);
        }

        if (in_array($address->getCountry(), ['FR', 'DE', 'ES', 'AT'])) {
            return Price::fromEuros(12.99);
        }

        return Price::fromEuros(25.99);
    }
}
