<?php

/**
 * Test standalone del pattern Materialized View
 * 
 * Questo file dimostra come testare il pattern Materialized View
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
if (!class_exists('App\Services\MaterializedViewService')) {
    require_once 'app/Services/MaterializedViewService.php';
}

use App\Services\MaterializedViewService;

echo "=== TEST PATTERN MATERIALIZED VIEW ===\n\n";

// Test del pattern Materialized View
$service = new MaterializedViewService();

echo "1. Test creazione viste materializzate...\n";
try {
    $results = $service->createAllViews();
    echo "   ✓ Viste create: " . count($results) . "\n";
    
    foreach ($results as $viewName => $result) {
        echo "   - {$viewName}: {$result}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nella creazione: " . $e->getMessage() . "\n";
}

echo "\n2. Test aggiornamento viste...\n";
try {
    $results = $service->refreshAllViews();
    echo "   ✓ Viste aggiornate: " . count($results) . "\n";
    
    foreach ($results as $viewName => $result) {
        echo "   - {$viewName}: {$result}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nell'aggiornamento: " . $e->getMessage() . "\n";
}

echo "\n3. Test lettura dati dalle viste...\n";
try {
    $categoryData = $service->getViewData('sales_by_category');
    echo "   ✓ Dati categorie: " . count($categoryData) . " righe\n";
    
    $monthData = $service->getViewData('sales_by_month');
    echo "   ✓ Dati mensili: " . count($monthData) . " righe\n";
    
    $productsData = $service->getViewData('top_products');
    echo "   ✓ Dati prodotti: " . count($productsData) . " righe\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nella lettura: " . $e->getMessage() . "\n";
}

echo "\n4. Test statistiche delle viste...\n";
try {
    $stats = $service->getViewStats('sales_by_category');
    echo "   ✓ Statistiche categoria:\n";
    echo "   - Vista: " . $stats['view_name'] . "\n";
    echo "   - Tabella: " . $stats['table_name'] . "\n";
    echo "   - Righe: " . $stats['row_count'] . "\n";
    echo "   - Frequenza: " . $stats['refresh_frequency'] . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nelle statistiche: " . $e->getMessage() . "\n";
}

echo "\n5. Test stato di tutte le viste...\n";
try {
    $status = $service->getAllViewsStatus();
    echo "   ✓ Stato viste: " . count($status) . " viste\n";
    
    foreach ($status as $viewName => $viewStatus) {
        if (isset($viewStatus['error'])) {
            echo "   - {$viewName}: ERRORE - {$viewStatus['error']}\n";
        } else {
            echo "   - {$viewName}: {$viewStatus['row_count']} righe\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nello stato: " . $e->getMessage() . "\n";
}

echo "\n6. Test completo del pattern...\n";
try {
    $results = $service->testMaterializedView();
    echo "   ✓ Test completo eseguito\n";
    echo "   ✓ Risultati: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nel test completo: " . $e->getMessage() . "\n";
}

echo "\n7. Test performance...\n";
try {
    $start = microtime(true);
    
    // Simula query complessa
    $categoryData = $service->getViewData('sales_by_category');
    $monthData = $service->getViewData('sales_by_month');
    $productsData = $service->getViewData('top_products');
    
    $totalTime = microtime(true) - $start;
    $totalRows = count($categoryData) + count($monthData) + count($productsData);
    
    echo "   ✓ Test di performance completato\n";
    echo "   ✓ Tempo totale: " . number_format($totalTime, 4) . " secondi\n";
    echo "   ✓ Righe recuperate: {$totalRows}\n";
    echo "   ✓ Righe al secondo: " . number_format($totalRows / $totalTime, 2) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test di performance: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern Materialized View.\n";
echo "Per un test completo con database reale, usa l'integrazione Laravel.\n";
echo "Le viste materializzate migliorano significativamente le performance per query complesse.\n";
