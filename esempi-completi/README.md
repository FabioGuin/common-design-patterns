# Esempi Completi

## Cosa trovi qui
Questa cartella contiene progetti Laravel completi e funzionanti che mostrano come usare i pattern di design nella pratica. Ogni esempio è un progetto vero che puoi scaricare e usare.

## Perché sono utili
- Codice funzionante che puoi copiare e incollare
- Esempi reali di come integrare i pattern in Laravel
- Riferimento per i tuoi progetti futuri

## Gli esempi che hai a disposizione

### Logger Singleton
- **Cartella**: `01-singleton-logger/`
- **Pattern**: Singleton
- **Cosa fa**: Un sistema di logging che usa una sola istanza per tutta l'app
- **Cosa include**: 
  - Logger service singleton funzionante
  - Salvataggio dei log su file
  - Integrazione con Laravel Service Container
  - API per gestire i log

### Gestione Utenti con Factory
- **Cartella**: `02-factory-user-management/`
- **Pattern**: Factory Method
- **Cosa fa**: Sistema per creare diversi tipi di utenti usando le factory
- **Cosa include**:
  - Factory per diversi tipi di utenti
  - Factory per le notifiche
  - Seeding automatico del database

### Sistema di Pagamento Multi-Provider
- **Cartella**: `04-abstract-factory-payment/`
- **Pattern**: Abstract Factory
- **Cosa fa**: Sistema di pagamento che funziona con Stripe, PayPal e altri
- **Cosa include**:
  - Gruppi di classi che vanno insieme (Stripe, PayPal)
  - Gateway, Validator e Logger compatibili
  - Configurazione dinamica dei provider
  - API RESTful completa

### Repository Pattern
- **Cartella**: `03-repository-pattern/`
- **Pattern**: Repository, Unit of Work
- **Cosa fa**: Astrae l'accesso ai dati e gestisce le transazioni
- **Cosa include**:
  - Interface e implementazioni dei repository
  - Unit of Work per le transazioni
  - Caching integrato
  - Test con mock

### Service Layer
- **Cartella**: `04-service-layer/`
- **Pattern**: Service Layer, DTO
- **Cosa fa**: Separa la logica business dal resto dell'applicazione
- **Cosa include**:
  - Logica business separata
  - Data Transfer Objects
  - Validazione integrata
  - API RESTful

### Sistema Chat con IA
- **Cartella**: `05-ai-chat-system/`
- **Pattern**: AI Gateway, AI Response Caching, AI Context Management
- **Cosa fa**: Sistema di chat che integra l'intelligenza artificiale
- **Cosa include**:
  - Integrazione con OpenAI/Claude
  - Cache delle risposte
  - Gestione del contesto della conversazione
  - Streaming delle risposte

### Event Sourcing
- **Cartella**: `06-event-sourcing/`
- **Pattern**: Event Sourcing, CQRS
- **Cosa fa**: Salva tutti gli eventi invece dello stato finale
- **Cosa include**:
  - Event store
  - Separazione Command/Query
  - Ricostruzione delle proiezioni
  - Audit trail completo

### API Microservizi
- **Cartella**: `07-microservices-api/`
- **Pattern**: API Gateway, Circuit Breaker, Service Discovery
- **Cosa fa**: Architettura a microservizi con Laravel
- **Cosa include**:
  - API Gateway
  - Service mesh
  - Circuit breaker pattern
  - Distributed tracing

### Implementazione CQRS
- **Cartella**: `08-cqrs-implementation/`
- **Pattern**: CQRS, Event Sourcing, Saga
- **Cosa fa**: Separazione completa tra comandi e query
- **Cosa include**:
  - Separazione Command/Query
  - Event store
  - Orchestrazione Saga
  - Ottimizzazione dei modelli di lettura

## Come usare gli esempi

1. **Scarica l'esempio** nella cartella che preferisci
2. **Installa le dipendenze** con `composer install`
3. **Configura l'ambiente** copiando `.env.example` in `.env`
4. **Esegui le migrazioni** con `php artisan migrate`
5. **Avvia il server** con `php artisan serve`

## Struttura di ogni esempio

```
esempio-completo/
├── README.md                 # Documentazione specifica
├── app/                      # Codice applicazione
├── config/                   # Configurazioni
├── database/                 # Migrazioni e seeders
├── routes/                   # Definizione routes
├── tests/                    # Test suite
├── composer.json             # Dipendenze
└── .env.example              # Configurazione ambiente
```

## Link utili
- [Torna all'indice principale](../README.md)
- [Pattern Creazionali](../01-pattern-creazionali/)
- [Pattern Laravel-Specifici](../05-pattern-laravel-specifici/)
