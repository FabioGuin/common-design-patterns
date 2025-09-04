# Connection Pool System

## Cosa fa

Sistema completo di gestione connessioni database usando l'Object Pool Pattern. Gestisce automaticamente un pool di connessioni PDO, ottimizzando le performance e controllando l'uso delle risorse.

## Perché è utile

- **Performance**: Evita il costo di creare/distruggere connessioni
- **Controllo risorse**: Limita il numero di connessioni attive
- **Gestione automatica**: Rilascia automaticamente le connessioni
- **Monitoraggio**: Statistiche in tempo reale del pool
- **Recovery**: Gestione automatica degli errori

## Struttura del progetto

```
07-connection-pool-system/
├── app/
│   ├── Services/
│   │   ├── ConnectionPool.php          # Pool principale
│   │   ├── PoolManager.php             # Gestione del pool
│   │   └── DatabaseService.php         # Servizio di esempio
│   ├── Http/
│   │   └── Controllers/
│   │       └── PoolController.php      # API per monitoraggio
│   └── Providers/
│       └── PoolServiceProvider.php     # Registrazione servizi
├── routes/
│   ├── web.php                         # Route web
│   └── api.php                         # Route API
├── tests/
│   └── Feature/
│       └── ConnectionPoolTest.php      # Test completi
├── composer.json                       # Dipendenze
└── README.md                          # Questo file
```

## Installazione

1. **Copia i file** nella tua applicazione Laravel
2. **Registra il service provider** in `config/app.php`:
   ```php
   'providers' => [
       // ...
       App\Providers\PoolServiceProvider::class,
   ],
   ```
3. **Configura il database** in `.env`
4. **Installa le dipendenze**:
   ```bash
   composer install
   ```
5. **Esegui i test**:
   ```bash
   php artisan test tests/Feature/ConnectionPoolTest.php
   ```
6. **Test rapido**:
   ```bash
   php test-example.php
   ```

## Uso base

```php
use App\Services\ConnectionPool;

// Crea il pool
$pool = new ConnectionPool('mysql', 5);

// Usa una connessione
$connection = $pool->acquire();
$stmt = $connection->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();
$pool->release($connection);
```

## API di monitoraggio

- `GET /api/pool/stats` - Statistiche del pool
- `GET /api/pool/health` - Stato di salute del pool
- `POST /api/pool/reset` - Reset del pool

## Test

Il progetto include test completi con Pest che verificano:
- Creazione e gestione del pool
- Acquire e release delle connessioni
- Gestione degli errori
- Performance e limiti
- Integrazione con Laravel

## Esempi di utilizzo

### Processamento batch
```php
$service = new DatabaseService();
$results = $service->processUsers([1, 2, 3, 4, 5]);
```

### Monitoraggio in tempo reale
```php
$stats = $pool->getStats();
echo "Connessioni disponibili: " . $stats['available'];
echo "Connessioni in uso: " . $stats['in_use'];
```

## Configurazione avanzata

Puoi configurare:
- Dimensione massima del pool
- Timeout delle connessioni
- Retry automatico
- Logging dettagliato
- Metriche di performance

## Esempi di utilizzo avanzato

### Pool multipli per diversi scopi
```php
$poolManager = PoolManager::getInstance();

// Pool per operazioni di lettura
$readPool = $poolManager->createPool('read-only', 'mysql_read', 5);

// Pool per operazioni di scrittura
$writePool = $poolManager->createPool('write-only', 'mysql_write', 3);

// Pool per operazioni batch
$batchPool = $poolManager->createPool('batch', 'mysql', 10);
```

### Monitoraggio in tempo reale
```php
// Statistiche globali
$globalStats = $poolManager->getGlobalStats();
echo "Utilizzo globale: " . $globalStats['global_utilization_percentage'] . "%";

// Stato di salute
$health = $poolManager->getGlobalHealthStatus();
if ($health['status'] === 'critical') {
    // Invia alert
}
```

### Gestione automatica degli errori
```php
$service = new DatabaseService('default');

try {
    $users = $service->processUsers([1, 2, 3, 4, 5]);
    echo "Processati: " . $users['successful'] . " utenti";
} catch (Exception $e) {
    // Il pool gestisce automaticamente il recovery
    Log::error("Errore processamento: " . $e->getMessage());
}
```

## API Endpoints

- `GET /api/pool/stats` - Statistiche di tutti i pool
- `GET /api/pool/stats/{name}` - Statistiche di un pool specifico
- `GET /api/pool/health` - Stato di salute globale
- `GET /api/pool/health/{name}` - Stato di salute di un pool
- `POST /api/pool/create` - Crea un nuovo pool
- `POST /api/pool/reset/{name}` - Reset di un pool
- `POST /api/pool/reset-all` - Reset di tutti i pool