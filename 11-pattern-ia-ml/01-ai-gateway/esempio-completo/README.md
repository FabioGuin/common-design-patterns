# AI Gateway Pattern - Esempio per Integrazione Laravel

## Cosa fa questo esempio
Questo esempio dimostra il pattern AI Gateway in Laravel attraverso un sistema unificato per gestire multiple API di intelligenza artificiale. L'esempio include:

- **Un Gateway unificato** che astrae le differenze tra provider AI
- **Provider multipli** (OpenAI, Claude, Gemini) con interfaccia comune
- **Un Controller** che testa il pattern via browser e API
- **Una vista interattiva** che permette di testare diversi provider
- **Test completi** che verificano il corretto funzionamento del pattern

## Come funziona l'esempio
L'AI Gateway creato gestisce:
- **Astrazione** delle differenze tra provider AI
- **Fallback automatico** tra provider in caso di errori
- **Rate limiting** per controllare l'uso delle API
- **Caching** per ottimizzare le performance
- **Logging** per monitorare l'utilizzo

Quando testi l'esempio, vedrai che:
1. Puoi inviare richieste AI senza sapere quale provider usare
2. Il sistema gestisce automaticamente il fallback
3. Le risposte vengono cachate per performance migliori
4. Il rate limiting previene l'abuso delle API

## Caratteristiche tecniche
- Interfaccia comune per tutti i provider AI
- Sistema di fallback automatico
- Rate limiting configurabile
- Caching delle risposte
- Logging completo delle richieste
- Controller per testare il pattern via browser e API
- Vista interattiva per dimostrare il gateway
- Test PHPUnit completi

## Prerequisiti
- **Progetto Laravel 11+** già installato e funzionante
- **PHP 8.2+** (requisito di Laravel 11)
- **API Keys** per i provider AI (opzionale per il test)

## Integrazione nel tuo progetto Laravel

### 1. Copia i file (sostituisci `/path/to/your/laravel` con il percorso del tuo progetto)

```bash
# Vai nella directory del tuo progetto Laravel
cd /path/to/your/laravel

# Copia i file necessari
cp /path/to/this/example/app/Models/AIRequest.php app/Models/
cp /path/to/this/example/app/Services/AI/AIGatewayService.php app/Services/AI/
cp /path/to/this/example/app/Services/AI/Providers/AIProviderInterface.php app/Services/AI/Providers/
cp /path/to/this/example/app/Services/AI/Providers/OpenAIProvider.php app/Services/AI/Providers/
cp /path/to/this/example/app/Services/AI/Providers/ClaudeProvider.php app/Services/AI/Providers/
cp /path/to/this/example/app/Services/AI/Providers/GeminiProvider.php app/Services/AI/Providers/
cp /path/to/this/example/app/Http/Controllers/AIGatewayController.php app/Http/Controllers/
mkdir -p resources/views/ai-gateway
cp /path/to/this/example/resources/views/ai-gateway/example.blade.php resources/views/ai-gateway/
cp /path/to/this/example/tests/Feature/AIGatewayTest.php tests/Feature/
```

### 2. Aggiungi le route

Aggiungi queste righe al tuo `routes/web.php`:

```php
use App\Http\Controllers\AIGatewayController;

// Route per il pattern AI Gateway
Route::get('/ai-gateway', [AIGatewayController::class, 'show']);
Route::get('/ai-gateway/test', [AIGatewayController::class, 'test']);
Route::post('/ai-gateway/chat', [AIGatewayController::class, 'chat']);

// Route API
Route::prefix('api/ai-gateway')->group(function () {
    Route::get('/', [AIGatewayController::class, 'index']);
    Route::post('/chat', [AIGatewayController::class, 'chat']);
    Route::get('/test', [AIGatewayController::class, 'test']);
});
```

### 3. Configura le API Keys (opzionale)

Aggiungi al tuo `.env`:

```env
OPENAI_API_KEY=your_openai_key_here
CLAUDE_API_KEY=your_claude_key_here
GEMINI_API_KEY=your_gemini_key_here
```

### 4. Testa l'integrazione

```bash
# Avvia il server Laravel
php artisan serve

# Visita la pagina di test
open http://localhost:8000/ai-gateway

# Testa via API
curl http://localhost:8000/api/ai-gateway/test

# Esegui i test
php artisan test tests/Feature/AIGatewayTest.php
```

### 5. Verifica che tutto funzioni

1. **Browser**: Vai su `http://localhost:8000/ai-gateway` e testa il gateway
2. **API**: Esegui `curl http://localhost:8000/api/ai-gateway/test`
3. **Test**: Esegui `php artisan test tests/Feature/AIGatewayTest.php`

Se tutto funziona, l'integrazione è completata! 🎉

## File inclusi

- `app/Models/AIRequest.php` - Modello per le richieste AI
- `app/Services/AI/AIGatewayService.php` - Servizio principale del gateway
- `app/Services/AI/Providers/AIProviderInterface.php` - Interfaccia per i provider
- `app/Services/AI/Providers/OpenAIProvider.php` - Provider OpenAI
- `app/Services/AI/Providers/ClaudeProvider.php` - Provider Claude
- `app/Services/AI/Providers/GeminiProvider.php` - Provider Gemini
- `app/Http/Controllers/AIGatewayController.php` - Controller per testare il pattern
- `resources/views/ai-gateway/example.blade.php` - Vista interattiva per il browser
- `tests/Feature/AIGatewayTest.php` - Test PHPUnit completi
- `routes/web.php` - Route da integrare nel tuo progetto

## Personalizzazione

### Configurazione
Modifica i provider in `app/Services/AI/Providers/` per personalizzare l'integrazione con le API.

### Estensione
Aggiungi nuovi provider implementando `AIProviderInterface`.

## Note importanti
- L'AI Gateway astrae le differenze tra provider AI
- Gestisce automaticamente il fallback tra provider
- Include rate limiting e caching per ottimizzare le performance
- I file sono pronti per essere copiati in un progetto Laravel esistente
