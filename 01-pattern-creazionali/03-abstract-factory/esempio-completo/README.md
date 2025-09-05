# Abstract Factory Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern Abstract Factory in Laravel attraverso un sistema di creazione di componenti UI per diverse tematiche. L'esempio include:

- **Un'interfaccia Abstract Factory** che definisce il contratto per creare famiglie di oggetti correlati
- **Factory concrete** che implementano la creazione di componenti per tematiche specifiche (Dark, Light, Colorful)
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare la creazione di componenti UI
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'Abstract Factory creato gestisce:
- **Creazione di famiglie di componenti** UI (Button, Card, Modal) per diverse tematiche
- **Coerenza visiva** tra componenti della stessa famiglia
- **Facilità di cambio tema** senza modificare il codice client
- **Estensibilità** per aggiungere nuove tematiche facilmente

Quando testi l'esempio, vedrai che:
1. Ogni factory crea componenti coerenti per la sua tematica
2. I componenti della stessa famiglia hanno stili coordinati
3. È facile cambiare tema creando una nuova factory
4. Il codice è flessibile e mantenibile

## Caratteristiche tecniche
- Interfaccia Abstract Factory per definire il contratto
- Factory concrete per ogni famiglia di oggetti
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare la creazione di componenti
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
cp /path/to/this/example/app/Models/UIComponent.php app/Models/
cp /path/to/this/example/app/Services/UIAbstractFactory.php app/Services/
cp /path/to/this/example/app/Http/Controllers/UIComponentController.php app/Http/Controllers/
mkdir -p resources/views/abstract-factory
cp /path/to/this/example/resources/views/abstract-factory/example.blade.php resources/views/abstract-factory/
cp /path/to/this/example/tests/Feature/UIAbstractFactoryTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\UIComponentController;

// Route per il pattern Abstract Factory
Route::get('/abstract-factory', [UIComponentController::class, 'show']);
Route::get('/abstract-factory/test', [UIComponentController::class, 'test']);
Route::post('/abstract-factory/create', [UIComponentController::class, 'createComponents']);

// Route API
Route::prefix('api/abstract-factory')->group(function () {
    Route::get('/', [UIComponentController::class, 'index']);
    Route::post('/create', [UIComponentController::class, 'createComponents']);
    Route::get('/test', [UIComponentController::class, 'test']);
});
```

### 3. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/abstract-factory

# Testa via API
curl http://localhost:8000/api/abstract-factory/test

# Esegui i test
php artisan test tests/Feature/UIAbstractFactoryTest.php
```

### 4. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/abstract-factory` e testa la creazione di componenti
2. **API**: Esegui `curl http://localhost:8000/api/abstract-factory/test`
3. **Test**: Esegui `php artisan test tests/Feature/UIAbstractFactoryTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/UIComponent.php` - Modello per componenti UI
- `app/Services/UIAbstractFactory.php` - Abstract Factory per creare componenti
- `app/Http/Controllers/UIComponentController.php` - Controller per testare il pattern
- `resources/views/abstract-factory/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/UIAbstractFactoryTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica le factory in `app/Services/UIAbstractFactory.php` per personalizzare i componenti creati.

### Estensione
Aggiungi nuove tematiche creando nuove factory concrete che implementano `UIAbstractFactoryInterface`.

## Note importanti
- L'Abstract Factory crea famiglie di oggetti correlati
- Ogni factory è responsabile di una famiglia specifica
- È facile estendere il sistema aggiungendo nuove factory
- I file sono pronti per essere copiati in un progetto Laravel esistente
