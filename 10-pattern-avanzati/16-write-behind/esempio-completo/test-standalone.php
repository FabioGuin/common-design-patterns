<?php

/**
 * Test standalone del pattern Write-Behind
 * 
 * Questo file dimostra come testare il pattern Write-Behind
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
if (!class_exists('App\Services\WriteBehindService')) {
    require_once 'app/Services/WriteBehindService.php';
}

use App\Services\WriteBehindService;

echo "=== TEST PATTERN WRITE-BEHIND ===\n\n";

// Test del pattern Write-Behind
$service = new WriteBehindService();

echo "1. Test scrittura immediata in cache...\n";
try {
    $testData = [
        'level' => 'info',
        'message' => 'Test Write-Behind',
        'context' => ['test' => true],
        'user_id' => 1,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent'
    ];
    
    $id = $service->write('log', $testData);
    echo "   ✓ Scrittura completata con ID: {$id}\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nella scrittura: " . $e->getMessage() . "\n";
}

echo "\n2. Test lettura dalla cache...\n";
try {
    $cached = $service->read('log', $id ?? 'test_id');
    if ($cached) {
        echo "   ✓ Lettura dalla cache completata\n";
        echo "   ✓ Dati: " . json_encode($cached) . "\n";
    } else {
        echo "   ✗ Nessun dato trovato in cache\n";
    }
} catch (Exception $e) {
    echo "   ✗ Errore nella lettura: " . $e->getMessage() . "\n";
}

echo "\n3. Test aggiornamento in cache...\n";
try {
    $updateData = [
        'message' => 'Updated Test Write-Behind',
        'level' => 'warning'
    ];
    
    $updated = $service->update('log', $id ?? 'test_id', $updateData);
    if ($updated) {
        echo "   ✓ Aggiornamento completato\n";
    } else {
        echo "   ✗ Aggiornamento fallito\n";
    }
} catch (Exception $e) {
    echo "   ✗ Errore nell'aggiornamento: " . $e->getMessage() . "\n";
}

echo "\n4. Test batch processing...\n";
try {
    $batchData = [];
    for ($i = 0; $i < 10; $i++) {
        $batchData[] = [
            'level' => 'info',
            'message' => "Batch test {$i}",
            'context' => ['batch_test' => true, 'iteration' => $i],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ];
    }
    
    $results = $service->writeBatch('log', $batchData);
    $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
    echo "   ✓ Batch processing completato\n";
    echo "   ✓ Successi: {$successCount}/10\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel batch processing: " . $e->getMessage() . "\n";
}

echo "\n5. Test di performance...\n";
try {
    $start = microtime(true);
    
    for ($i = 0; $i < 100; $i++) {
        $service->write('log', [
            'level' => 'info',
            'message' => "Performance test {$i}",
            'context' => ['performance_test' => true, 'iteration' => $i],
            'user_id' => 1,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);
    }
    
    $totalTime = microtime(true) - $start;
    $writesPerSecond = 100 / $totalTime;
    
    echo "   ✓ Test di performance completato\n";
    echo "   ✓ Tempo totale: " . number_format($totalTime, 4) . " secondi\n";
    echo "   ✓ Scritture al secondo: " . number_format($writesPerSecond, 2) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test di performance: " . $e->getMessage() . "\n";
}

echo "\n6. Test completo del pattern...\n";
try {
    $results = $service->testWriteBehind();
    echo "   ✓ Test completo eseguito\n";
    echo "   ✓ Risultati: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nel test completo: " . $e->getMessage() . "\n";
}

echo "\n7. Statistiche del pattern...\n";
try {
    $stats = $service->getStats();
    echo "   ✓ Statistiche: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nelle statistiche: " . $e->getMessage() . "\n";
}

echo "\n8. Informazioni sulla coda...\n";
try {
    $queueInfo = $service->getQueueInfo();
    echo "   ✓ Info coda: " . json_encode($queueInfo, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "   ✗ Errore nelle info coda: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern Write-Behind.\n";
echo "Per un test completo con database e cache reali, usa l'integrazione Laravel.\n";
echo "Ricorda di avviare il queue worker: php artisan queue:work\n";
