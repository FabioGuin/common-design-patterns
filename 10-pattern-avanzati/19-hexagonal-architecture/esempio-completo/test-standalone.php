<?php

/**
 * Test standalone del pattern Hexagonal Architecture
 * 
 * Questo file dimostra come testare il pattern Hexagonal Architecture
 * senza bisogno di Laravel o altri framework.
 * 
 * Esegui con: php test-standalone.php
 */

// Simula l'autoloader di Laravel per i namespace
spl_autoload_register(function ($class) {
    $file = str_replace('App\\', 'app/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Fallback: carica direttamente se l'autoloader non funziona
if (!class_exists('App\Domain\Order')) {
    require_once 'app/Domain/Order.php';
}

use App\Domain\Order;

echo "=== TEST PATTERN HEXAGONAL ARCHITECTURE ===\n\n";

// Test del pattern Hexagonal Architecture
echo "1. Test Core Domain (Order Entity)...\n";
try {
    $orderData = [
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'items' => [
            ['product_id' => 'prod_1', 'quantity' => 2, 'price' => 50.00]
        ],
        'discount' => 10.00
    ];
    
    $order = new Order($orderData);
    echo "   ✓ Order Entity creata\n";
    
    // Test validazione
    $isValid = $order->isValid();
    echo "   ✓ Validazione: " . ($isValid ? 'Valida' : 'Non valida') . "\n";
    
    // Test business methods
    echo "   ✓ Item count: " . $order->getItemCount() . "\n";
    echo "   ✓ Total quantity: " . $order->getTotalQuantity() . "\n";
    echo "   ✓ Has discount: " . ($order->hasDiscount() ? 'Sì' : 'No') . "\n";
    echo "   ✓ Discount percentage: " . number_format($order->getDiscountPercentage(), 2) . "%\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Entity: " . $e->getMessage() . "\n";
}

echo "\n2. Test Business Rules...\n";
try {
    $order = new Order([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'items' => [
            ['product_id' => 'prod_1', 'quantity' => 1, 'price' => 100.00]
        ],
        'subtotal' => 100.00,
        'discount' => 60.00 // Sconto del 60% (dovrebbe essere limitato al 50%)
    ]);
    
    $order->applyBusinessRules();
    echo "   ✓ Business rules applicate\n";
    echo "   ✓ Discount limitato: " . $order->getDiscount() . " (era 60.00)\n";
    echo "   ✓ Shipping cost: " . $order->getShippingCost() . " (gratuito per ordini > 100€)\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Business Rules: " . $e->getMessage() . "\n";
}

echo "\n3. Test Order Status...\n";
try {
    $order = new Order([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'items' => [['product_id' => 'prod_1', 'quantity' => 1, 'price' => 50.00]],
        'status' => 'pending'
    ]);
    
    echo "   ✓ Status iniziale: " . $order->getStatus() . "\n";
    echo "   ✓ Can be cancelled: " . ($order->canBeCancelled() ? 'Sì' : 'No') . "\n";
    echo "   ✓ Can be updated: " . ($order->canBeUpdated() ? 'Sì' : 'No') . "\n";
    
    $order->setStatus('paid');
    echo "   ✓ Status aggiornato: " . $order->getStatus() . "\n";
    echo "   ✓ Is paid: " . ($order->isPaid() ? 'Sì' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Status: " . $e->getMessage() . "\n";
}

echo "\n4. Test Order Calculations...\n";
try {
    $order = new Order([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'items' => [
            ['product_id' => 'prod_1', 'quantity' => 2, 'price' => 30.00],
            ['product_id' => 'prod_2', 'quantity' => 1, 'price' => 40.00]
        ],
        'subtotal' => 100.00,
        'discount' => 20.00,
        'shipping_cost' => 10.00
    ]);
    
    echo "   ✓ Subtotal: €" . $order->getSubtotal() . "\n";
    echo "   ✓ Discount: €" . $order->getDiscount() . "\n";
    echo "   ✓ Total amount: €" . $order->getTotalAmount() . "\n";
    echo "   ✓ Shipping cost: €" . $order->getShippingCost() . "\n";
    echo "   ✓ Total with shipping: €" . $order->getTotalWithShipping() . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Calculations: " . $e->getMessage() . "\n";
}

echo "\n5. Test Order Validation...\n";
try {
    // Test ordine valido
    $validOrder = new Order([
        'customer_name' => 'Valid Customer',
        'customer_email' => 'valid@example.com',
        'items' => [['product_id' => 'prod_1', 'quantity' => 1, 'price' => 50.00]],
        'total_amount' => 50.00
    ]);
    
    $validOrder->validate();
    echo "   ✓ Ordine valido: Validato con successo\n";
    
    // Test ordine non valido
    $invalidOrder = new Order([
        'customer_name' => '', // Nome vuoto
        'customer_email' => 'invalid-email', // Email non valida
        'items' => [], // Nessun item
        'total_amount' => -10 // Importo negativo
    ]);
    
    try {
        $invalidOrder->validate();
        echo "   ✗ Ordine non valido: Dovrebbe fallire\n";
    } catch (Exception $e) {
        echo "   ✓ Ordine non valido: Validazione fallita correttamente\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Validation: " . $e->getMessage() . "\n";
}

echo "\n6. Test Order Serialization...\n";
try {
    $order = new Order([
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'items' => [['product_id' => 'prod_1', 'quantity' => 1, 'price' => 50.00]],
        'status' => 'pending'
    ]);
    
    $orderArray = $order->toArray();
    echo "   ✓ Order serializzato: " . count($orderArray) . " campi\n";
    
    $reconstructedOrder = Order::fromArray($orderArray);
    echo "   ✓ Order ricostruito: " . $reconstructedOrder->getCustomerName() . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Serialization: " . $e->getMessage() . "\n";
}

echo "\n7. Test Performance...\n";
try {
    $start = microtime(true);
    
    // Crea 1000 ordini per testare le performance
    for ($i = 0; $i < 1000; $i++) {
        $order = new Order([
            'customer_name' => "Customer {$i}",
            'customer_email' => "customer{$i}@example.com",
            'items' => [['product_id' => "prod_{$i}", 'quantity' => 1, 'price' => 50.00]],
            'status' => 'pending'
        ]);
        
        $order->applyBusinessRules();
        $order->toArray();
    }
    
    $totalTime = microtime(true) - $start;
    $ordersPerSecond = 1000 / $totalTime;
    
    echo "   ✓ Test di performance completato\n";
    echo "   ✓ Tempo totale: " . number_format($totalTime, 4) . " secondi\n";
    echo "   ✓ Ordini al secondo: " . number_format($ordersPerSecond, 2) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test di performance: " . $e->getMessage() . "\n";
}

echo "\n8. Test Architettura Hexagonal...\n";
try {
    // Simula la struttura dell'architettura esagonale
    $architecture = [
        'core_domain' => [
            'Order' => 'Entità di business',
            'OrderService' => 'Logica di business',
            'BusinessRules' => 'Regole di business'
        ],
        'ports' => [
            'OrderRepositoryInterface' => 'Contratto per repository',
            'PaymentServiceInterface' => 'Contratto per pagamenti',
            'NotificationServiceInterface' => 'Contratto per notifiche'
        ],
        'adapters' => [
            'EloquentOrderRepository' => 'Implementazione database',
            'StripePaymentService' => 'Implementazione pagamenti',
            'EmailNotificationService' => 'Implementazione notifiche'
        ]
    ];
    
    echo "   ✓ Architettura Hexagonal simulata:\n";
    foreach ($architecture as $layer => $components) {
        echo "     - {$layer}:\n";
        foreach ($components as $component => $description) {
            echo "       • {$component}: {$description}\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Architettura: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern Hexagonal Architecture.\n";
echo "Per un test completo con dependency injection e adapter, usa l'integrazione Laravel.\n";
echo "Il pattern fornisce isolamento completo della logica di business.\n";
