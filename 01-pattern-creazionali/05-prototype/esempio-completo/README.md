# Prototype Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Prototype in Laravel attraverso un sistema di clonazione di documenti. L'esempio include:

- **Un'interfaccia Prototype** che definisce il metodo di clonazione
- **Prototipi concreti** che implementano la clonazione per diversi tipi di documenti
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di clonare documenti
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
Il Prototype creato gestisce:
- **Clonazione di documenti** complessi senza ricreare da zero
- **Personalizzazione** di documenti clonati
- **Performance** migliorata per oggetti costosi da creare
- **Flessibilità** nella creazione di varianti

Quando testi l'esempio, vedrai che:
1. Puoi clonare documenti esistenti come template
2. I documenti clonati mantengono la struttura originale
3. Puoi personalizzare i documenti clonati
4. È più efficiente della creazione da zero

## Caratteristiche tecniche
- Interfaccia Prototype per definire la clonazione
- Prototipi concreti per ogni tipo di documento
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare la clonazione
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
cp /path/to/this/example/app/Models/Document.php app/Models/
cp /path/to/this/example/app/Http/Controllers/DocumentController.php app/Http/Controllers/
mkdir -p resources/views/prototype
cp /path/to/this/example/resources/views/prototype/example.blade.php resources/views/prototype/
cp /path/to/this/example/tests/Feature/DocumentPrototypeTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\DocumentController;

// Route per il pattern Prototype
Route::get('/prototype', [DocumentController::class, 'show']);
Route::get('/prototype/test', [DocumentController::class, 'test']);
Route::post('/prototype/clone', [DocumentController::class, 'cloneDocument']);

// Route API
Route::prefix('api/prototype')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::post('/clone', [DocumentController::class, 'cloneDocument']);
    Route::get('/test', [DocumentController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/prototype

# Testa via API
curl http://localhost:8000/api/prototype/test

# Esegui i test
php artisan test tests/Feature/DocumentPrototypeTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/prototype` e testa la clonazione
2. **API**: Esegui `curl http://localhost:8000/api/prototype/test`
3. **Test**: Esegui `php artisan test tests/Feature/DocumentPrototypeTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/Document.php` - Modello per documenti con clonazione
- `app/Http/Controllers/DocumentController.php` - Controller per testare il pattern
- `resources/views/prototype/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/DocumentPrototypeTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica i prototipi in `app/Models/Document.php` per personalizzare i tipi di documenti.

### Estensione
Aggiungi nuovi tipi di documenti implementando l'interfaccia `DocumentPrototypeInterface`.

## Note importanti
- Il Prototype clona oggetti esistenti invece di crearli da zero
- È utile per oggetti costosi da creare
- I documenti clonati possono essere personalizzati
- I file sono pronti per essere copiati in un progetto Laravel esistente
