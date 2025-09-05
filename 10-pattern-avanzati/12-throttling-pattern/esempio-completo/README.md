# E-commerce Throttling Pattern

## Panoramica

Questo esempio dimostra l'implementazione del pattern Throttling in un sistema e-commerce Laravel. Il sistema implementa rate limiting per API pubbliche, throttling per tipo di utente e protezione da sovraccarico con diverse strategie di limitazione.

## Architettura

### Throttling Implementation
- **ThrottlingManager**: Gestisce i limiti di rate per ogni servizio
- **ThrottlingConfig**: Configurazione throttling per ogni servizio
- **RateLimiter**: Implementa diverse strategie di rate limiting
- **Storage**: Gestisce storage dei contatori

### Servizi con Throttling
- **PaymentService**: Throttling critico per servizi di pagamento
- **InventoryService**: Throttling importante per servizi di inventario
- **NotificationService**: Throttling non critico per servizi di notifiche
- **ApiService**: Throttling generico per API pubbliche

### Strategie di Throttling
- **Fixed Window**: Limite fisso per periodo di tempo
- **Sliding Window**: Finestra scorrevole per calcolo preciso
- **Token Bucket**: Bucket di token per gestione flessibile
- **Leaky Bucket**: Coda con processamento costante

## Struttura del Progetto

```
app/
├── Throttling/              # Throttling implementation
├── Services/                # Servizi con throttling
├── Strategies/              # Strategie di throttling
├── Config/                  # Configurazione throttling
├── Http/Controllers/        # Controllers per testare il pattern
└── Models/                  # Modelli per monitoring
```

## Funzionalità Implementate

### Throttling Core
- ✅ Gestione rate limiting per ogni servizio
- ✅ Throttling per tipo di utente
- ✅ Throttling per endpoint specifici
- ✅ Monitoring e metriche di throttling

### Servizi con Throttling
- ✅ PaymentService con throttling critico (5 req/min)
- ✅ InventoryService con throttling importante (20 req/min)
- ✅ NotificationService con throttling non critico (100 req/min)
- ✅ ApiService con throttling generico (1000 req/hour)

### Strategie di Throttling
- ✅ Fixed Window con limite fisso
- ✅ Sliding Window con finestra scorrevole
- ✅ Token Bucket con bucket di token
- ✅ Leaky Bucket con coda costante

### Monitoring
- ✅ Dashboard per stato throttling
- ✅ Metriche di rate limiting e blocchi
- ✅ Logging dettagliato per ogni throttling
- ✅ Alert per throttling eccessivi

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/throttling`
3. **Testa i servizi**:
   - Simula richieste normali
   - Simula richieste eccessive per testare throttling
   - Osserva gestione rate limiting
4. **Monitora throttling**:
   - Visualizza rate limiting e blocchi
   - Controlla strategie di throttling
   - Testa diversi tipi di utente

## Database

Il sistema usa tabelle per monitoring:
- **throttling_metrics**: Metriche di rate limiting
- **throttling_events**: Log dettagliato di ogni throttling
- **rate_limits**: Configurazione limiti per servizio

## Configurazione

Copia `env.example` in `.env` e configura:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=throttling
DB_USERNAME=root
DB_PASSWORD=

# Throttling Configuration
THROTTLING_DEFAULT_RATE=100
THROTTLING_DEFAULT_WINDOW=3600
THROTTLING_PAYMENT_RATE=5
THROTTLING_INVENTORY_RATE=20
THROTTLING_NOTIFICATION_RATE=100
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Throttling Pattern**: Limita il numero di richieste per periodo
- **Rate Limiting**: Controllo del traffico per servizio
- **User-based Throttling**: Limiti diversi per tipo di utente
- **Endpoint Throttling**: Protezione granulare delle risorse
- **Protection**: Sistema protetto da sovraccarico e abusi

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Throttling**: Implementazione custom per gestione rate limiting
- **Strategies**: Strategie multiple per throttling
- **Storage**: Storage efficiente per contatori
- **Monitoring**: Dashboard per stato e metriche

## Vantaggi Dimostrati

- **Protezione**: Previene sovraccarico e abusi
- **Equità**: Garantisce accesso equo alle risorse
- **Stabilità**: Sistema più stabile e prevedibile
- **Sicurezza**: Protegge da attacchi di forza bruta
- **Controllo**: Gestione granulare del traffico
- **Scalabilità**: Sistema più scalabile
