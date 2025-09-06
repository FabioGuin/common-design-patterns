# E-commerce Timeout Pattern

## Panoramica

Questo esempio dimostra l'implementazione del pattern Timeout in un sistema e-commerce Laravel. Il sistema implementa timeout dinamici per servizi esterni, timeout con retry integrato e circuit breaker con timeout per proteggere il sistema da operazioni lente.

## Architettura

### Timeout Implementation
- **TimeoutManager**: Gestisce i timeout per ogni servizio
- **TimeoutConfig**: Configurazione timeout per ogni servizio
- **TimeoutStrategy**: Strategie di timeout diverse
- **CircuitBreaker**: Integrazione con circuit breaker

### Servizi con Timeout
- **PaymentService**: Timeout critico per servizi di pagamento
- **InventoryService**: Timeout importante per servizi di inventario
- **NotificationService**: Timeout non critico per servizi di notifiche
- **ExternalApiService**: Timeout generico per chiamate API esterne

### Strategie di Timeout
- **Simple Timeout**: Timeout fisso per tutte le operazioni
- **Dynamic Timeout**: Timeout basato sul tipo di servizio
- **Retry Timeout**: Timeout per ogni tentativo di retry
- **Circuit Timeout**: Timeout integrato con circuit breaker

## Struttura del Progetto

```
app/
├── Timeout/                 # Timeout implementation
├── Services/                # Servizi con timeout
├── Strategies/              # Strategie di timeout
├── Config/                  # Configurazione timeout
├── Http/Controllers/        # Controllers per testare il pattern
└── Models/                  # Modelli per monitoring
```

## Funzionalità Implementate

### Timeout Core
-  Gestione timeout dinamici per ogni servizio
-  Timeout con retry integrato
-  Circuit breaker con timeout
-  Monitoring e metriche di timeout

### Servizi con Timeout
-  PaymentService con timeout critico (15s)
-  InventoryService con timeout importante (10s)
-  NotificationService con timeout non critico (5s)
-  ExternalApiService con timeout generico (30s)

### Strategie di Timeout
-  Simple Timeout con limite fisso
-  Dynamic Timeout basato sul servizio
-  Retry Timeout per ogni tentativo
-  Circuit Timeout per prevenire timeout eccessivi

### Monitoring
-  Dashboard per stato timeout
-  Metriche di timeout e successi
-  Logging dettagliato per ogni timeout
-  Alert per timeout eccessivi

## Come Testare

1. **Avvia il server**: `php artisan serve`
2. **Vai su**: `http://localhost:8000/timeout`
3. **Testa i servizi**:
   - Simula operazioni normali
   - Simula operazioni lente per testare timeout
   - Osserva gestione timeout
4. **Monitora timeout**:
   - Visualizza timeout e successi
   - Controlla strategie di timeout
   - Testa circuit breaker

## Database

Il sistema usa tabelle per monitoring:
- **timeout_metrics**: Metriche di timeout e successi
- **timeout_events**: Log dettagliato di ogni timeout
- **circuit_breakers**: Stato dei circuit breaker

## Configurazione

Copia `env.example` in `.env` e configura:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=timeout
DB_USERNAME=root
DB_PASSWORD=

# Timeout Configuration
TIMEOUT_DEFAULT=30000
TIMEOUT_PAYMENT=15000
TIMEOUT_INVENTORY=10000
TIMEOUT_NOTIFICATION=5000
```

## Migrazioni

```bash
php artisan migrate
```

## Pattern Implementato

Questo esempio dimostra:
- **Timeout Pattern**: Termina automaticamente operazioni lente
- **Dynamic Timeout**: Timeout diversi per ogni servizio
- **Retry Timeout**: Timeout per ogni tentativo di retry
- **Circuit Breaker**: Integrazione con circuit breaker
- **Resilience**: Sistema più reattivo e stabile

## Note Tecniche

- **Laravel 11**: Framework aggiornato
- **Timeout**: Implementazione custom per gestione timeout
- **Dynamic Configuration**: Timeout configurabili per servizio
- **Circuit Breaker**: Integrazione per prevenire timeout eccessivi
- **Monitoring**: Dashboard per stato e metriche

## Vantaggi Dimostrati

- **Protezione**: Previene operazioni infinite e blocchi
- **Reattività**: Sistema più reattivo e stabile
- **Risorse**: Protegge risorse da operazioni lente
- **UX**: Migliore esperienza utente
- **Debugging**: Più facile identificare problemi di performance
- **Scalabilità**: Sistema più scalabile
