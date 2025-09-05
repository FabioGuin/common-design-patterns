<?php

/**
 * Test standalone del pattern CQRS + Event Sourcing
 * 
 * Questo file dimostra come testare il pattern CQRS + Event Sourcing
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
if (!class_exists('App\Services\EventStoreService')) {
    require_once 'app/Services/EventStoreService.php';
}

use App\Services\EventStoreService;

echo "=== TEST PATTERN CQRS + EVENT SOURCING ===\n\n";

// Test del pattern CQRS + Event Sourcing
$eventStore = new EventStoreService();

echo "1. Test Event Store...\n";
try {
    $results = $eventStore->testCqrsEventSourcing();
    echo "   ✓ Event Store test completato\n";
    
    foreach ($results as $test => $result) {
        if (is_array($result)) {
            echo "   - {$test}: " . json_encode($result) . "\n";
        } else {
            echo "   - {$test}: {$result}\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test Event Store: " . $e->getMessage() . "\n";
}

echo "\n2. Test creazione evento...\n";
try {
    // Simula un evento di test
    $testEvent = new class {
        public function toArray() {
            return [
                'event_id' => uniqid('event_', true),
                'event_type' => 'TestEvent',
                'data' => ['test' => true, 'message' => 'Test event'],
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => 1
            ];
        }
    };
    
    $eventId = $eventStore->appendEvent('test_aggregate', $testEvent);
    echo "   ✓ Evento creato con ID: {$eventId}\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nella creazione evento: " . $e->getMessage() . "\n";
}

echo "\n3. Test recupero eventi...\n";
try {
    $events = $eventStore->getEvents('test_aggregate');
    echo "   ✓ Eventi recuperati: " . count($events) . "\n";
    
    foreach ($events as $event) {
        echo "   - {$event['event_type']}: {$event['event_id']}\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nel recupero eventi: " . $e->getMessage() . "\n";
}

echo "\n4. Test verifica esistenza aggregate...\n";
try {
    $exists = $eventStore->aggregateExists('test_aggregate');
    echo "   ✓ Aggregate esiste: " . ($exists ? 'Sì' : 'No') . "\n";
    
    $notExists = $eventStore->aggregateExists('non_existent_aggregate');
    echo "   ✓ Aggregate inesistente: " . ($notExists ? 'Sì' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nella verifica aggregate: " . $e->getMessage() . "\n";
}

echo "\n5. Test statistiche Event Store...\n";
try {
    $stats = $eventStore->getStats();
    echo "   ✓ Statistiche recuperate:\n";
    
    foreach ($stats as $key => $value) {
        if (is_array($value)) {
            echo "   - {$key}: " . json_encode($value) . "\n";
        } else {
            echo "   - {$key}: {$value}\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Errore nelle statistiche: " . $e->getMessage() . "\n";
}

echo "\n6. Test replay eventi...\n";
try {
    $replayResult = $eventStore->replayEvents('test_aggregate');
    echo "   ✓ Replay completato: " . ($replayResult['success'] ? 'Successo' : 'Fallito') . "\n";
    echo "   ✓ Eventi riprodotti: " . $replayResult['events_replayed'] . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel replay: " . $e->getMessage() . "\n";
}

echo "\n7. Test eventi per tipo...\n";
try {
    $testEvents = $eventStore->getEventsByType('TestEvent');
    echo "   ✓ Eventi TestEvent trovati: " . count($testEvents) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel recupero per tipo: " . $e->getMessage() . "\n";
}

echo "\n8. Test eventi in range di date...\n";
try {
    $startDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $endDate = date('Y-m-d H:i:s');
    
    $eventsInRange = $eventStore->getEventsInDateRange($startDate, $endDate);
    echo "   ✓ Eventi nell'ultima ora: " . count($eventsInRange) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel recupero per data: " . $e->getMessage() . "\n";
}

echo "\n9. Test performance...\n";
try {
    $start = microtime(true);
    
    // Simula creazione di 100 eventi
    for ($i = 0; $i < 100; $i++) {
        $testEvent = new class($i) {
            private $index;
            
            public function __construct($index) {
                $this->index = $index;
            }
            
            public function toArray() {
                return [
                    'event_id' => uniqid('perf_', true),
                    'event_type' => 'PerformanceTest',
                    'data' => ['index' => $this->index, 'message' => 'Performance test event'],
                    'timestamp' => date('Y-m-d H:i:s'),
                    'version' => 1
                ];
            }
        };
        
        $eventStore->appendEvent('perf_aggregate', $testEvent);
    }
    
    $totalTime = microtime(true) - $start;
    $eventsPerSecond = 100 / $totalTime;
    
    echo "   ✓ Test di performance completato\n";
    echo "   ✓ Tempo totale: " . number_format($totalTime, 4) . " secondi\n";
    echo "   ✓ Eventi al secondo: " . number_format($eventsPerSecond, 2) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel test di performance: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETATO ===\n";
echo "\nNota: Questo test dimostra la logica del pattern CQRS + Event Sourcing.\n";
echo "Per un test completo con database reale e projection, usa l'integrazione Laravel.\n";
echo "Il pattern fornisce audit completo e tracciabilità totale di ogni operazione.\n";
