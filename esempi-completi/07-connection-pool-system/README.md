# Connection Pool System - Esempio Completo

## Cosa fa questo esempio
Questo esempio dimostra l'implementazione dell'**Object Pool Pattern** per gestire connessioni di database e risorse costose in Laravel. Il sistema mantiene una piscina di connessioni pre-create e le riutilizza per migliorare le performance.

## Caratteristiche principali
- **Connection Pool**: Gestione di pool di connessioni database
- **Resource Pool**: Pool per oggetti costosi (file, cache, etc.)
- **Auto-scaling**: Aggiunta automatica di connessioni quando necessario
- **Health Check**: Monitoraggio dello stato delle connessioni
- **Statistics**: Statistiche di utilizzo del pool
- **Test**: Test completi con Pest
- **API**: Endpoint REST per dimostrare l'uso

## Struttura del progetto
```
app/
├── Services/
│   ├── ConnectionPool.php           # Pool principale per connessioni
│   ├── ResourcePool.php             # Pool generico per risorse
│   ├── PoolManager.php              # Gestore di pool multipli
│   └── PoolStatistics.php           # Statistiche del pool
├── Models/
│   ├── DatabaseConnection.php       # Modello connessione database
│   ├── FileConnection.php           # Modello connessione file
│   └── CacheConnection.php          # Modello connessione cache
├── Http/
│   └── Controllers/
│       └── PoolController.php       # Controller per API
└── Traits/
    └── Poolable.php                 # Trait per oggetti poolabili

database/
├── migrations/
│   ├── create_connection_logs_table.php
│   └── create_pool_statistics_table.php
└── seeders/
    └── PoolSeeder.php

tests/
└── Feature/
    └── ConnectionPoolTest.php       # Test completi

routes/
└── api.php                          # Route API
```

## Come usarlo

### 1. Installazione
```bash
composer install
php artisan migrate
php artisan db:seed
```

### 2. Esempi di uso

#### Pool di connessioni database
```php
$pool = new ConnectionPool('mysql', 10);
$connection = $pool->acquire();
$result = $connection->query('SELECT * FROM users');
$pool->release($connection);
```

#### Pool di risorse generiche
```php
$pool = new ResourcePool(FileConnection::class, 5);
$file = $pool->acquire();
$content = $file->read('file.txt');
$pool->release($file);
```

#### Gestione automatica
```php
$poolManager = new PoolManager();
$poolManager->addPool('database', new ConnectionPool('mysql', 10));
$poolManager->addPool('files', new ResourcePool(FileConnection::class, 5));

$connection = $poolManager->acquire('database');
$file = $poolManager->acquire('files');
```

### 3. API Endpoints
- `GET /api/pools` - Lista tutti i pool
- `GET /api/pools/{name}/stats` - Statistiche di un pool
- `POST /api/pools/{name}/acquire` - Acquisisce una risorsa
- `POST /api/pools/{name}/release` - Rilascia una risorsa
- `GET /api/pools/{name}/health` - Controllo salute del pool

### 4. Test
```bash
php artisan test
```

## Vantaggi dell'Object Pool Pattern
- **Performance**: Evita la creazione costante di oggetti costosi
- **Efficienza**: Riutilizzo intelligente delle risorse
- **Scalabilità**: Gestione automatica del carico
- **Controllo**: Limite del numero di oggetti attivi
- **Monitoraggio**: Statistiche e health check

## Pattern correlati
- **Singleton**: Per gestire il pool come istanza unica
- **Factory Method**: Per creare nuovi oggetti quando necessario
- **Observer**: Per monitorare lo stato del pool
