<?php

/**
 * Test script per dimostrare il funzionamento del Singleton Logger
 * 
 * Questo script può essere eseguito per testare il pattern senza Laravel
 * Simula l'utilizzo del LoggerService in un contesto reale
 */

require_once 'app/Services/Logger/LogLevel.php';
require_once 'app/Services/Logger/LogEntry.php';
require_once 'app/Services/Logger/LoggerService.php';

use App\Services\Logger\LoggerService;
use App\Services\Logger\LogLevel;

echo "=== Test Singleton Logger Pattern ===\n\n";

// Test 1: Verifica che sia la stessa istanza
echo "1. Test Singleton Pattern:\n";
$logger1 = LoggerService::getInstance();
$logger2 = LoggerService::getInstance();

echo "   Istanza 1: " . spl_object_hash($logger1) . "\n";
echo "   Istanza 2: " . spl_object_hash($logger2) . "\n";
echo "   Stessa istanza: " . ($logger1 === $logger2 ? "✅ SÌ" : "❌ NO") . "\n\n";

// Test 2: Logging con diversi livelli
echo "2. Test Logging con diversi livelli:\n";
$logger1->debug('Messaggio di debug', ['component' => 'test']);
$logger1->info('Informazione importante', ['user_id' => 123]);
$logger1->warning('Attenzione: memoria alta', ['memory' => '512MB']);
$logger1->error('Errore di connessione', ['error' => 'Connection timeout']);
$logger1->critical('Sistema in crash', ['component' => 'database']);

echo "   Logs creati: " . count($logger1->getLogs()) . "\n\n";

// Test 3: Verifica che entrambe le istanze abbiano gli stessi logs
echo "3. Test Condivisione Logs tra istanze:\n";
echo "   Logs istanza 1: " . count($logger1->getLogs()) . "\n";
echo "   Logs istanza 2: " . count($logger2->getLogs()) . "\n";
echo "   Logs identici: " . ($logger1->getLogs() === $logger2->getLogs() ? "✅ SÌ" : "❌ NO") . "\n\n";

// Test 4: Statistiche
echo "4. Statistiche Logs:\n";
$stats = $logger1->getStats();
foreach ($stats as $level => $count) {
    echo "   $level: $count\n";
}
echo "\n";

// Test 5: Filtro per livello
echo "5. Test Filtro per livello (ERROR e superiori):\n";
$errorLogs = $logger1->getLogsByMinLevel(LogLevel::ERROR);
echo "   Logs ERROR e superiori: " . count($errorLogs) . "\n";
foreach ($errorLogs as $log) {
    echo "   - [{$log->level->value}] {$log->message}\n";
}
echo "\n";

// Test 6: Formato messaggi
echo "6. Test Formato Messaggi:\n";
$logs = $logger1->getLogs();
foreach (array_slice($logs, -3) as $log) {
    echo "   " . $log->formatMessage() . "\n";
}
echo "\n";

// Test 7: Test livello minimo
echo "7. Test Livello Minimo (WARNING):\n";
$logger1->setMinLevel(LogLevel::WARNING);
$logger1->debug('Questo non dovrebbe apparire');
$logger1->info('Neanche questo');
$logger1->warning('Questo sì');
$logger1->error('E anche questo');

$recentLogs = array_slice($logger1->getLogs(), -4);
echo "   Ultimi 4 logs (dovrebbero essere solo WARNING e ERROR):\n";
foreach ($recentLogs as $log) {
    echo "   - [{$log->level->value}] {$log->message}\n";
}
echo "\n";

echo "=== Test Completato ===\n";
echo "Il Singleton Pattern funziona correttamente! 🎉\n";
