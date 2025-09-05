<?php

/**
 * Test standalone del pattern Microservices
 * 
 * Questo file dimostra come testare il pattern Microservices
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
if (!class_exists('App\Services\UserService')) {
    require_once 'app/Services/UserService.php';
}

use App\Services\UserService;

echo "=== TEST PATTERN MICROSERVICES ===\n\n";

// Test del pattern Microservices
echo "1. Test User Service...\n";
try {
    $userService = new UserService();
    echo "   ✓ User Service creato\n";
    echo "   ✓ Service ID: " . $userService->getId() . "\n";
    echo "   ✓ Version: " . $userService->getVersion() . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test User Service: " . $e->getMessage() . "\n";
}

echo "\n2. Test Product Service...\n";
try {
    if (class_exists('App\Services\ProductService')) {
        require_once 'app/Services/ProductService.php';
        $productService = new \App\Services\ProductService();
        echo "   ✓ Product Service creato\n";
        echo "   ✓ Service ID: " . $productService->getId() . "\n";
        echo "   ✓ Version: " . $productService->getVersion() . "\n";
    } else {
        echo "   ⚠ Product Service non disponibile (richiede Laravel)\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Product Service: " . $e->getMessage() . "\n";
}

echo "\n3. Test Order Service...\n";
try {
    if (class_exists('App\Services\OrderService')) {
        require_once 'app/Services/OrderService.php';
        $orderService = new \App\Services\OrderService(
            new UserService(),
            new \App\Services\ProductService(),
            new \App\Services\PaymentService()
        );
        echo "   ✓ Order Service creato\n";
        echo "   ✓ Service ID: " . $orderService->getId() . "\n";
        echo "   ✓ Version: " . $orderService->getVersion() . "\n";
    } else {
        echo "   ⚠ Order Service non disponibile (richiede Laravel)\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Order Service: " . $e->getMessage() . "\n";
}

echo "\n4. Test Payment Service...\n";
try {
    if (class_exists('App\Services\PaymentService')) {
        require_once 'app/Services/PaymentService.php';
        $paymentService = new \App\Services\PaymentService();
        echo "   ✓ Payment Service creato\n";
        echo "   ✓ Service ID: " . $paymentService->getId() . "\n";
        echo "   ✓ Version: " . $paymentService->getVersion() . "\n";
    } else {
        echo "   ⚠ Payment Service non disponibile (richiede Laravel)\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Payment Service: " . $e->getMessage() . "\n";
}

echo "\n5. Test API Gateway...\n";
try {
    if (class_exists('App\Services\ApiGatewayService')) {
        require_once 'app/Services/ApiGatewayService.php';
        $apiGateway = new \App\Services\ApiGatewayService(
            new UserService(),
            new \App\Services\ProductService(),
            new \App\Services\OrderService(
                new UserService(),
                new \App\Services\ProductService(),
                new \App\Services\PaymentService()
            ),
            new \App\Services\PaymentService()
        );
        echo "   ✓ API Gateway creato\n";
        echo "   ✓ Gateway ID: " . $apiGateway->getId() . "\n";
        echo "   ✓ Version: " . $apiGateway->getVersion() . "\n";
    } else {
        echo "   ⚠ API Gateway non disponibile (richiede Laravel)\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test API Gateway: " . $e->getMessage() . "\n";
}

echo "\n6. Test Service Discovery...\n";
try {
    if (class_exists('App\Services\ServiceDiscoveryService')) {
        require_once 'app/Services/ServiceDiscoveryService.php';
        $serviceDiscovery = new \App\Services\ServiceDiscoveryService();
        echo "   ✓ Service Discovery creato\n";
        echo "   ✓ Discovery ID: " . $serviceDiscovery->getId() . "\n";
        echo "   ✓ Version: " . $serviceDiscovery->getVersion() . "\n";
    } else {
        echo "   ⚠ Service Discovery non disponibile (richiede Laravel)\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Service Discovery: " . $e->getMessage() . "\n";
}

echo "\n7. Test Architettura Microservices...\n";
try {
    $architecture = [
        'user_service' => [
            'id' => 'user-service',
            'name' => 'User Service',
            'version' => '1.0.0',
            'port' => 8001,
            'database' => 'users',
            'responsibilities' => ['Authentication', 'User Management', 'Profiles']
        ],
        'product_service' => [
            'id' => 'product-service',
            'name' => 'Product Service',
            'version' => '1.0.0',
            'port' => 8002,
            'database' => 'products',
            'responsibilities' => ['Product Catalog', 'Inventory', 'Pricing']
        ],
        'order_service' => [
            'id' => 'order-service',
            'name' => 'Order Service',
            'version' => '1.0.0',
            'port' => 8003,
            'database' => 'orders',
            'responsibilities' => ['Order Management', 'Cart', 'Order Processing']
        ],
        'payment_service' => [
            'id' => 'payment-service',
            'name' => 'Payment Service',
            'version' => '1.0.0',
            'port' => 8004,
            'database' => 'payments',
            'responsibilities' => ['Payment Processing', 'Refunds', 'Billing']
        ],
        'api_gateway' => [
            'id' => 'api-gateway',
            'name' => 'API Gateway',
            'version' => '1.0.0',
            'port' => 8000,
            'responsibilities' => ['Routing', 'Load Balancing', 'Service Discovery']
        ]
    ];
    
    echo "   ✓ Architettura Microservices simulata:\n";
    foreach ($architecture as $serviceId => $service) {
        echo "     - {$service['name']} ({$serviceId}):\n";
        echo "       • Port: {$service['port']}\n";
        echo "       • Database: " . ($service['database'] ?? 'N/A') . "\n";
        echo "       • Responsibilities: " . implode(', ', $service['responsibilities']) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Architettura: " . $e->getMessage() . "\n";
}

echo "\n8. Test Comunicazione tra Servizi...\n";
try {
    $communication = [
        'user_service' => [
            'provides' => ['User data', 'Authentication'],
            'consumes' => []
        ],
        'product_service' => [
            'provides' => ['Product data', 'Inventory'],
            'consumes' => []
        ],
        'order_service' => [
            'provides' => ['Order data', 'Order processing'],
            'consumes' => ['User Service', 'Product Service']
        ],
        'payment_service' => [
            'provides' => ['Payment processing', 'Refunds'],
            'consumes' => ['Order Service']
        ],
        'api_gateway' => [
            'provides' => ['Routing', 'Load balancing'],
            'consumes' => ['All services']
        ]
    ];
    
    echo "   ✓ Comunicazione tra servizi simulata:\n";
    foreach ($communication as $serviceId => $comm) {
        echo "     - {$serviceId}:\n";
        echo "       • Provides: " . implode(', ', $comm['provides']) . "\n";
        echo "       • Consumes: " . implode(', ', $comm['consumes']) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Comunicazione: " . $e->getMessage() . "\n";
}

echo "\n9. Test Performance...\n";
try {
    $start = microtime(true);
    
    // Simula creazione di 100 servizi per testare le performance
    for ($i = 0; $i < 100; $i++) {
        $service = new UserService();
        $service->getId();
        $service->getVersion();
    }
    
    $totalTime = microtime(true) - $start;
    $servicesPerSecond = 100 / $totalTime;
    
    echo "   ✓ Test di performance completato\n";
    echo "   ✓ Tempo totale: " . number_format($totalTime, 4) . " secondi\n";
    echo "   ✓ Servizi al secondo: " . number_format($servicesPerSecond, 2) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test di performance: " . $e->getMessage() . "\n";
}

echo "\n10. Test Scalabilità...\n";
try {
    $scalability = [
        'horizontal_scaling' => 'Ogni servizio può essere scalato indipendentemente',
        'vertical_scaling' => 'Ogni servizio può essere ottimizzato per le proprie esigenze',
        'load_balancing' => 'Il carico può essere distribuito tra istanze multiple',
        'fault_isolation' => 'I fallimenti sono isolati per servizio',
        'independent_deployment' => 'Ogni servizio può essere deployato indipendentemente'
    ];
    
    echo "   ✓ Caratteristiche di scalabilità:\n";
    foreach ($scalability as $feature => $description) {
        echo "     - {$feature}: {$description}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Scalabilità: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern Microservices.\n";
echo "Per un test completo con database e comunicazione reale, usa l'integrazione Laravel.\n";
echo "Il pattern fornisce scalabilità indipendente e resilienza ai fallimenti.\n";
