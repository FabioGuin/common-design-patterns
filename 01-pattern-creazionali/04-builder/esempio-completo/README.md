# Builder Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Builder in Laravel attraverso un sistema di costruzione di email personalizzate. L'esempio include:

- **Un'interfaccia Builder** che definisce i passi per costruire oggetti complessi
- **Un Director** che orchestra il processo di costruzione
- **Builder concreti** che implementano la costruzione di diversi tipi di email
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di costruire email personalizzate
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il Builder creato gestisce:
- **Costruzione step-by-step** di email complesse
- **Flessibilità** nella configurazione di ogni componente
- **Riutilizzo** della logica di costruzione per diversi tipi
- **Validazione** e controllo della costruzione

Quando testi l'esempio, vedrai che:
1. Puoi costruire email complesse passo dopo passo
2. Ogni builder ha configurazioni specifiche per il tipo di email
3. Il director orchestra la costruzione in modo consistente
4. È facile aggiungere nuovi tipi di email

## Caratteristiche tecniche
- Interfaccia Builder per definire i passi di costruzione
- Director per orchestrare il processo
- Builder concreti per ogni tipo di oggetto
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare la costruzione
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
cp /path/to/this/example/app/Models/Email.php app/Models/
cp /path/to/this/example/app/Services/EmailBuilder.php app/Services/
cp /path/to/this/example/app/Http/Controllers/EmailController.php app/Http/Controllers/
mkdir -p resources/views/builder
cp /path/to/this/example/resources/views/builder/example.blade.php resources/views/builder/
cp /path/to/this/example/tests/Feature/EmailBuilderTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\EmailController;

// Route per il pattern Builder
Route::get('/builder', [EmailController::class, 'show']);
Route::get('/builder/test', [EmailController::class, 'test']);
Route::post('/builder/create', [EmailController::class, 'createEmail']);

// Route API
Route::prefix('api/builder')->group(function () {
    Route::get('/', [EmailController::class, 'index']);
    Route::post('/create', [EmailController::class, 'createEmail']);
    Route::get('/test', [EmailController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/builder

# Testa via API
curl http://localhost:8000/api/builder/test

# Esegui i test
php artisan test tests/Feature/EmailBuilderTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/builder` e testa la costruzione di email
2. **API**: Esegui `curl http://localhost:8000/api/builder/test`
3. **Test**: Esegui `php artisan test tests/Feature/EmailBuilderTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/Email.php` - Modello per email
- `app/Services/EmailBuilder.php` - Builder per costruire email
- `app/Http/Controllers/EmailController.php` - Controller per testare il pattern
- `resources/views/builder/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/EmailBuilderTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica i builder in `app/Services/EmailBuilder.php` per personalizzare i tipi di email.

### Estensione
Aggiungi nuovi tipi di email creando nuovi builder concreti che implementano `EmailBuilderInterface`.

## Note importanti
- Il Builder costruisce oggetti complessi passo dopo passo
- Il Director orchestra il processo di costruzione
- È facile estendere il sistema aggiungendo nuovi builder
- I file sono pronti per essere copiati in un progetto Laravel esistente
