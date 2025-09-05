# Singleton Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Singleton in Laravel attraverso un sistema di gestione istanze uniche. L'esempio include:

- **Un Model Singleton** che garantisce una sola istanza per tutta l'applicazione
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare il comportamento del Singleton
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il Singleton creato gestisce:
- **ID univoco** per ogni istanza (sempre lo stesso)
- **Contatore accessi** per tracciare quante volte viene utilizzato
- **Dati condivisi** che persistono tra tutte le chiamate
- **Protezione completa** contro clonazione e deserializzazione

Quando testi l'esempio, vedrai che:
1. Multiple chiamate a `getInstance()` restituiscono sempre la stessa istanza
2. I dati aggiunti tramite una "istanza" sono visibili da tutte le altre
3. Il contatore degli accessi si incrementa ad ogni operazione
4. Tentativi di clonazione o deserializzazione vengono bloccati

## Caratteristiche tecniche
- Implementazione classica del Singleton con costruttore privato
- Metodo getInstance() per ottenere l'istanza unica
- Protezione contro clonazione e deserializzazione
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il comportamento
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
cp /path/to/this/example/app/Models/SingletonModel.php app/Models/
cp /path/to/this/example/app/Http/Controllers/SingletonController.php app/Http/Controllers/
mkdir -p resources/views/singleton
cp /path/to/this/example/resources/views/singleton/example.blade.php resources/views/singleton/
cp /path/to/this/example/tests/Feature/SingletonTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\SingletonController;

// Route per il pattern Singleton
Route::get('/singleton', [SingletonController::class, 'show']);
Route::get('/singleton/test', [SingletonController::class, 'test']);
Route::get('/singleton/clone-test', [SingletonController::class, 'testClone']);

// Route API
Route::prefix('api/singleton')->group(function () {
    Route::get('/', [SingletonController::class, 'index']);
    Route::post('/test', [SingletonController::class, 'test']);
    Route::get('/clone-test', [SingletonController::class, 'testClone']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/singleton

# Testa via API
curl http://localhost:8000/api/singleton/test

# Esegui i test
php artisan test tests/Feature/SingletonTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/singleton` e clicca sui pulsanti di test
2. **API**: Esegui `curl http://localhost:8000/api/singleton/test`
3. **Test**: Esegui `php artisan test tests/Feature/SingletonTest.php`

Se tutto funziona, l'integrazione è completata! 🎉


## File inclusi

- `app/Models/SingletonModel.php` - Implementazione del pattern Singleton
- `app/Http/Controllers/SingletonController.php` - Controller per testare il pattern
- `resources/views/singleton/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/SingletonTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Il Singleton non richiede configurazione specifica, ma puoi modificare il comportamento nel metodo `getInstance()`.

### Estensione
Aggiungi nuovi metodi in `SingletonModel.php` per estendere le funzionalità del Singleton.

## Note importanti
- Il Singleton garantisce una sola istanza per tutta l'applicazione
- L'istanza viene creata solo al primo accesso (lazy loading)
- Non è possibile clonare o deserializzare l'istanza
- I file sono pronti per essere copiati in un progetto Laravel esistente
