# Object Pool Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Object Pool in Laravel attraverso un sistema di gestione di connessioni database. L'esempio include:

- **Un Object Pool** che gestisce un pool di oggetti riutilizzabili
- **Oggetti Poolable** che possono essere riutilizzati
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare il pool
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'Object Pool creato gestisce:
- **Riutilizzo di oggetti** costosi da creare (connessioni DB)
- **Gestione del ciclo di vita** degli oggetti
- **Performance** migliorata evitando creazioni multiple
- **Controllo delle risorse** limitate

Quando testi l'esempio, vedrai che:
1. Gli oggetti vengono riutilizzati dal pool
2. Il pool gestisce automaticamente il ciclo di vita
3. Le performance sono migliori rispetto alla creazione continua
4. Le risorse sono controllate e limitate

## Caratteristiche tecniche
- Object Pool per gestire oggetti riutilizzabili
- Interfaccia Poolable per oggetti che possono essere riutilizzati
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il pool
- Test PHPUnit completi

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/DatabaseConnection.php app/Models/
cp /path/to/this/example/app/Http/Controllers/ConnectionController.php app/Http/Controllers/
mkdir -p resources/views/object-pool
cp /path/to/this/example/resources/views/object-pool/example.blade.php resources/views/object-pool/
cp /path/to/this/example/tests/Feature/ConnectionPoolTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\ConnectionController;

// Route per il pattern Object Pool
Route::get('/object-pool', [ConnectionController::class, 'show']);
Route::get('/object-pool/test', [ConnectionController::class, 'test']);
Route::post('/object-pool/acquire', [ConnectionController::class, 'acquireConnection']);

// Route API
Route::prefix('api/object-pool')->group(function () {
    Route::get('/', [ConnectionController::class, 'index']);
    Route::post('/acquire', [ConnectionController::class, 'acquireConnection']);
    Route::get('/test', [ConnectionController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/object-pool

# Testa via API
curl http://localhost:8000/api/object-pool/test

# Esegui i test
php artisan test tests/Feature/ConnectionPoolTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/object-pool` e testa il pool
2. **API**: Esegui `curl http://localhost:8000/api/object-pool/test`
3. **Test**: Esegui `php artisan test tests/Feature/ConnectionPoolTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/DatabaseConnection.php` - Modello per connessioni con Object Pool
- `app/Http/Controllers/ConnectionController.php` - Controller per testare il pattern
- `resources/views/object-pool/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/ConnectionPoolTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica il pool in `app/Models/DatabaseConnection.php` per personalizzare la gestione degli oggetti.

### Estensione
Aggiungi nuovi tipi di oggetti poolable implementando l'interfaccia `PoolableInterface`.

## Note importanti
- L'Object Pool riutilizza oggetti costosi da creare
- Migliora le performance evitando creazioni multiple
- Gestisce automaticamente il ciclo di vita degli oggetti
- I file sono pronti per essere copiati in un progetto Laravel esistente
