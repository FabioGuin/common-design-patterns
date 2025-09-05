<?php

/**
 * Test standalone del pattern Write-Through
 * 
 * Questo file dimostra come testare il pattern Write-Through
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
if (!class_exists('App\Services\WriteThroughService')) {
    require_once 'app/Services/WriteThroughService.php';
}

use App\Services\WriteThroughService;

echo "=== TEST PATTERN WRITE-THROUGH ===\n\n";

// Test del pattern Write-Through
$service = new WriteThroughService();

echo "1. Test scrittura simultanea...\n";
try {
    $testData = [
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 99.99,
        'stock' => 10,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $service->write('product', $testData);
    echo "   ✓ Scrittura completata con ID: {$id}\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nella scrittura: " . $e->getMessage() . "\n";
}

echo "\n2. Test lettura dalla cache...\n";
try {
    $cached = $service->read('product', $id ?? 1);
    if ($cached) {
        echo "   ✓ Lettura dalla cache completata\n";
        echo "   ✓ Dati: " . json_encode($cached) . "\n";
    } else {
        echo "   ✗ Nessun dato trovato in cache\n";
    }
} catch (Exception $e) {
    echo "   ✗ Errore nella lettura: " . $e->getMessage() . "\n";
}

echo "\n3. Test aggiornamento simultaneo...\n";
try {
    $updateData = [
        'price' => 149.99,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $updated = $service->update('product', $id ?? 1, $updateData);
    if ($updated) {
        echo "   ✓ Aggiornamento completato\n";
    } else {
        echo "   ✗ Aggiornamento fallito\n";
    }
} catch (Exception $e) {
    echo "   ✗ Errore nell'aggiornamento: " . $e->getMessage() . "\n";
}

echo "\n4. Test eliminazione simultanea...\n";
try {
    $deleted = $service->delete('product', $id ?? 1);
    if ($deleted) {
        echo "   ✓ Eliminazione completata\n";
    } else {
        echo "   ✗ Eliminazione fallita\n";
    }
} catch (Exception $e) {
    echo "   ✗ Errore nell'eliminazione: " . $e->getMessage() . "\n";
}

echo "\n5. Test completo del pattern...\n";
try {
    $results = $service->testWriteThrough();
    echo "   ✓ Test completo eseguito\n";
    echo "   ✓ Risultati: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nel test completo: " . $e->getMessage() . "\n";
}

echo "\n6. Statistiche del pattern...\n";
try {
    $stats = $service->getStats();
    echo "   ✓ Statistiche: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nelle statistiche: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern Write-Through.\n";
echo "Per un test completo con database e cache reali, usa l'integrazione Laravel.\n";
