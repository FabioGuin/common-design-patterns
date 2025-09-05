# Factory Method Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Factory Method in Laravel attraverso un sistema di creazione di utenti con diversi ruoli. L'esempio include:

- **Un'interfaccia Factory** che definisce il contratto per creare oggetti
- **Factory concrete** che implementano la creazione di utenti specifici (Admin, Regular, Guest)
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare la creazione di diversi tipi di utenti
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il Factory Method creato gestisce:
- **Creazione di utenti** con ruoli diversi (Admin, Regular, Guest)
- **Logica di creazione** specifica per ogni tipo di utente
- **Validazione** e configurazione automatica per ogni ruolo
- **Estensibilità** per aggiungere nuovi tipi di utenti facilmente

Quando testi l'esempio, vedrai che:
1. Ogni factory crea utenti con configurazioni specifiche per il loro ruolo
2. La creazione è incapsulata e consistente per ogni tipo
3. È facile aggiungere nuovi tipi di utenti creando nuove factory
4. Il codice è flessibile e mantenibile

## Caratteristiche tecniche
- Interfaccia Factory Method per definire il contratto
- Factory concrete per ogni tipo di oggetto
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare la creazione di oggetti
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
cp /path/to/this/example/app/Models/User.php app/Models/
cp /path/to/this/example/app/Services/UserFactory.php app/Services/
cp /path/to/this/example/app/Http/Controllers/UserController.php app/Http/Controllers/
mkdir -p resources/views/factory-method
cp /path/to/this/example/resources/views/factory-method/example.blade.php resources/views/factory-method/
cp /path/to/this/example/tests/Feature/UserFactoryTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\UserController;

// Route per il pattern Factory Method
Route::get('/factory-method', [UserController::class, 'show']);
Route::get('/factory-method/test', [UserController::class, 'test']);
Route::post('/factory-method/create', [UserController::class, 'createUser']);

// Route API
Route::prefix('api/factory-method')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/create', [UserController::class, 'createUser']);
    Route::get('/test', [UserController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/factory-method

# Testa via API
curl http://localhost:8000/api/factory-method/test

# Esegui i test
php artisan test tests/Feature/UserFactoryTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/factory-method` e testa la creazione di utenti
2. **API**: Esegui `curl http://localhost:8000/api/factory-method/test`
3. **Test**: Esegui `php artisan test tests/Feature/UserFactoryTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/User.php` - Modello User con ruoli
- `app/Services/UserFactory.php` - Factory Method per creare utenti
- `app/Http/Controllers/UserController.php` - Controller per testare il pattern
- `resources/views/factory-method/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/UserFactoryTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica le factory in `app/Services/UserFactory.php` per personalizzare la creazione degli utenti.

### Estensione
Aggiungi nuovi tipi di utenti creando nuove factory concrete che implementano `UserFactoryInterface`.

## Note importanti
- Il Factory Method incapsula la logica di creazione degli oggetti
- Ogni factory è responsabile di un tipo specifico di oggetto
- È facile estendere il sistema aggiungendo nuove factory
- I file sono pronti per essere copiati in un progetto Laravel esistente
