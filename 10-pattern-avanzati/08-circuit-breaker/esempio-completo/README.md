# E-commerce Circuit Breaker

## Panoramica

Questo esempio dimostra l'implementazione del pattern Circuit Breaker in un sistema e-commerce Laravel. Il sistema protegge le chiamate a servizi esterni (pagamenti, inventario, notifiche) implementando circuit breaker con fallback strategies.

## Architettura

### Circuit Breaker Implementation
- **CircuitBreaker**: Classe principale per gestire stati e transizioni
- **CircuitBreakerManager**: Gestisce multiple istanze di circuit breaker
- **CircuitBreakerConfig**: Configurazione per ogni servizio

### Servizi Protetti
- **PaymentService**: Chiamate a servizi di pagamento esterni
- **InventoryService**: Chiamate a servizi di inventario esterni
- **NotificationService**: Chiamate a servizi di notifiche esterne
- **ShippingService**: Chiamate a servizi di spedizione esterni

### Fallback Strategies
- **PaymentFallback**: Gestisce pagamenti quando il servizio è down
- **InventoryFallback**: Gestisce inventario quando il servizio è down
- **NotificationFallback**: Gestisce notifiche quando il servizio è down
- **ShippingFallback**: Gestisce spedizioni quando il servizio è down

## Struttura del Progetto

```
app/
├── CircuitBreaker/        # Circuit breaker implementation
├── Services/              # Servizi esterni protetti
├── Fallbacks/             # Strategie di fallback
├── Config/                # Configurazione circuit breaker
├── Http/Controllers/      # Controllers per testare il pattern
└── Models/                # Modelli per monitoring
```

## Funzionalità Implementate

### Circuit Breaker Core
-  Gestione stati: Closed, Open, Half-Open
-  Configurazione per ogni servizio
-  Monitoring e metriche
-  Auto-recovery quando i servizi tornano online

### Servizi Protetti
-  PaymentService con circuit breaker
-  InventoryService con circuit breaker
-  NotificationService con circuit breaker
-  ShippingService con circuit breaker

### Fallback Strategies
-  Fallback per pagamenti (modalità offline)
-  Fallback per inventario (stima disponibilità)
-  Fallback per notifiche (coda locale)
-  Fallback per spedizioni (costi fissi)

### Monitoring
-  Dashboard per stato circuit breaker
-  Metriche di performance
-  Logging dettagliato
-  Alert per circuit aperti

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/circuit-breaker`
3. **Testa i servizi**:
   - Simula chiamate normali
   - Simula fallimenti per aprire circuit
   - Osserva fallback automatici
4. **Monitora stato**:
   - Visualizza stato circuit breaker
   - Controlla metriche e performance
   - Testa recupero automatico

## Database

Il sistema usa tabelle per monitoring:
- **circuit_breakers**: Stato e configurazione circuit breaker
- **circuit_breaker_metrics**: Metriche e statistiche
- **fallback_logs**: Log delle operazioni di fallback

## Configurazione

Copia `env.example` in `.env` e configura:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=circuit_breaker
DB_USERNAME=root
DB_PASSWORD=

# Circuit Breaker Configuration
CIRCUIT_BREAKER_FAILURE_THRESHOLD=5
CIRCUIT_BREAKER_TIMEOUT=60000
CIRCUIT_BREAKER_SUCCESS_THRESHOLD=3
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Circuit Breaker Pattern**: Protezione da servizi esterni problematici
- **Fallback Strategy**: Alternative graceful quando i servizi sono down
- **Monitoring**: Tracciamento stato e performance
- **Auto-recovery**: Recupero automatico quando i servizi tornano online
- **Resilience**: Sistema robusto e reattivo

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Circuit Breaker**: Implementazione custom per gestire stati
- **Fallback Strategies**: Alternative per ogni servizio
- **Monitoring**: Dashboard per stato e metriche
- **Configuration**: Configurazione flessibile per ogni servizio

## Vantaggi Dimostrati

- **Protezione**: Previene cascading failures e sovraccarico
- **Performance**: Fallimenti rapidi invece di timeout lunghi
- **Resilienza**: Sistema più robusto e reattivo
- **Fallback**: Alternative graceful quando i servizi sono down
- **Monitoring**: Visibilità completa dello stato dei servizi
- **Auto-recovery**: Recupero automatico senza intervento manuale
