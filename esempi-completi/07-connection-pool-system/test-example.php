<?php

require_once 'vendor/autoload.php';

use App\Services\ConnectionPool;
use App\Services\ResourcePool;
use App\Services\PoolManager;
use App\Models\DatabaseConnection;
use App\Models\FileConnection;
use App\Models\CacheConnection;

// Simula l'ambiente Laravel per il test
echo "=== ESEMPIO OBJECT POOL PATTERN ===\n\n";

echo "1. Creazione pool di connessioni database:\n";
$dbPool = new ConnectionPool('mysql', 5);
echo "Pool creato: {$dbPool->getConnectionName()} (max: {$dbPool->getMaxSize()})\n\n";

echo "2. Acquisizione e utilizzo connessioni:\n";
$connection1 = $dbPool->acquire('user1');
echo "Connessione 1 acquisita da: {$connection1->getAcquiredBy()}\n";

$connection2 = $dbPool->acquire('user2');
echo "Connessione 2 acquisita da: {$connection2->getAcquiredBy()}\n";

$stats = $dbPool->getStats();
echo "Statistiche pool: {$stats['in_use']} in uso, {$stats['available']} disponibili\n\n";

echo "3. Rilascio connessioni:\n";
$dbPool->release($connection1);
$dbPool->release($connection2);
echo "Connessioni rilasciate\n";

$stats = $dbPool->getStats();
echo "Statistiche pool: {$stats['in_use']} in uso, {$stats['available']} disponibili\n\n";

echo "4. Test pool di risorse generiche (File):\n";
$filePool = new ResourcePool(FileConnection::class, 3, ['/tmp/test.txt', 'w']);
echo "File pool creato: {$filePool->getResourceClass()} (max: {$filePool->getMaxSize()})\n";

$file1 = $filePool->acquire('file_user');
echo "File acquisito da: {$file1->getAcquiredBy()}\n";

$filePool->release($file1);
echo "File rilasciato\n\n";

echo "5. Test PoolManager con pool multipli:\n";
$poolManager = new PoolManager();

// Aggiungi pool
$poolManager->addPool('database', new ConnectionPool('mysql', 5));
$poolManager->addPool('files', new ResourcePool(FileConnection::class, 3, ['/tmp/test.txt', 'w']));
$poolManager->addPool('cache', new ResourcePool(CacheConnection::class, 8, ['default']));

echo "Pool aggiunti: " . implode(', ', $poolManager->getPoolNames()) . "\n";
echo "Totale pool: {$poolManager->getTotalPools()}\n\n";

echo "6. Acquisizione da pool multipli:\n";
$dbConnection = $poolManager->acquire('database', 'multi_user');
$fileConnection = $poolManager->acquire('files', 'multi_user');
$cacheConnection = $poolManager->acquire('cache', 'multi_user');

echo "Risorse acquisite da pool multipli:\n";
echo "- Database: " . get_class($dbConnection) . "\n";
echo "- File: " . get_class($fileConnection) . "\n";
echo "- Cache: " . get_class($cacheConnection) . "\n\n";

echo "7. Statistiche dettagliate:\n";
$allStats = $poolManager->getAllStats();
foreach ($allStats as $poolName => $poolStats) {
    echo "Pool {$poolName}:\n";
    echo "  - Risorse totali: {$poolStats['total']}\n";
    echo "  - In uso: {$poolStats['in_use']}\n";
    echo "  - Disponibili: {$poolStats['available']}\n";
    echo "  - Utilizzo: " . number_format($poolStats['utilization'], 1) . "%\n";
    echo "  - Success rate: " . number_format($poolStats['success_rate'], 1) . "%\n";
}
echo "\n";

echo "8. Health check:\n";
$health = $poolManager->healthCheck();
foreach ($health as $poolName => $poolHealth) {
    echo "Pool {$poolName}: {$poolHealth['status']} ({$poolHealth['health_percentage']}% healthy)\n";
}
echo "\n";

echo "9. Rilascio risorse:\n";
$poolManager->release('database', $dbConnection);
$poolManager->release('files', $fileConnection);
$poolManager->release('cache', $cacheConnection);
echo "Tutte le risorse rilasciate\n\n";

echo "10. Test stress con pool:\n";
$stressPool = new ConnectionPool('mysql', 3);
$connections = [];

echo "Acquisizione massiva di connessioni:\n";
for ($i = 1; $i <= 3; $i++) {
    $connections[] = $stressPool->acquire("stress_user_{$i}");
    echo "Connessione {$i} acquisita\n";
}

$stats = $stressPool->getStats();
echo "Statistiche stress test: {$stats['in_use']} in uso, {$stats['available']} disponibili\n";

echo "Rilascio massivo:\n";
foreach ($connections as $i => $connection) {
    $stressPool->release($connection);
    echo "Connessione " . ($i + 1) . " rilasciata\n";
}

$stats = $stressPool->getStats();
echo "Statistiche finali: {$stats['in_use']} in uso, {$stats['available']} disponibili\n\n";

echo "11. Test durata utilizzo:\n";
$testConnection = $stressPool->acquire('duration_test');
echo "Connessione acquisita per test durata\n";

// Simula utilizzo
sleep(1);

$duration = $testConnection->getUsageDuration();
echo "Durata utilizzo: {$duration} secondi\n";

$stressPool->release($testConnection);
echo "Connessione rilasciata\n\n";

echo "12. Test cleanup e reset:\n";
$cleanupPool = new ConnectionPool('mysql', 5);

// Acquisisci e rilascia alcune connessioni
$conn1 = $cleanupPool->acquire('cleanup_test');
$conn2 = $cleanupPool->acquire('cleanup_test');
$cleanupPool->release($conn1);
$cleanupPool->release($conn2);

$statsBefore = $cleanupPool->getStats();
echo "Prima del cleanup: {$statsBefore['available']} disponibili\n";

$removed = $cleanupPool->cleanup();
echo "Cleanup completato: {$removed} connessioni rimosse\n";

$cleanupPool->reset();
$statsAfter = $cleanupPool->getStats();
echo "Dopo reset: {$statsAfter['available']} disponibili, {$statsAfter['in_use']} in uso\n\n";

echo "13. Test gestione errori:\n";
$errorPool = new ConnectionPool('mysql', 1);
$errorConnection = $errorPool->acquire('error_test');

echo "Connessione acquisita per test errori\n";

try {
    $errorPool->acquire('error_test_2');
    echo "ERRORE: Dovrebbe essere fallito!\n";
} catch (Exception $e) {
    echo "Errore catturato come previsto: " . $e->getMessage() . "\n";
}

$errorStats = $errorPool->getStats();
echo "Statistiche errori: {$errorStats['failed']} fallimenti\n\n";

echo "14. Test configurazioni diverse:\n";
$poolManager->addPool('small', new ConnectionPool('mysql', 2));
$poolManager->addPool('large', new ConnectionPool('mysql', 10));

$configStats = $poolManager->getAllStats();
echo "Pool small: max {$configStats['small']['max_size']}\n";
echo "Pool large: max {$configStats['large']['max_size']}\n";
echo "Totale risorse: {$poolManager->getTotalResources()}\n\n";

echo "=== FINE ESEMPIO ===\n";
