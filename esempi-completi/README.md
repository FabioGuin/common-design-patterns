# Esempi Completi

## 📝 Descrizione
Questa cartella contiene implementazioni complete e funzionanti dei pattern di design applicati a Laravel. Ogni esempio è un progetto Laravel completo che dimostra l'applicazione pratica dei pattern.

## 🎯 Obiettivo
- Fornire esempi pratici e funzionanti
- Dimostrare l'integrazione dei pattern in progetti reali
- Servire come riferimento per implementazioni future

## 📋 Esempi Disponibili

### 01. Singleton Logger
- **Cartella**: `01-singleton-logger/`
- **Pattern**: Singleton
- **Descrizione**: Sistema di logging personalizzato con Singleton Pattern
- **Caratteristiche**: 
  - Logger service singleton
  - Persistenza su file
  - Integrazione con Laravel Service Container
  - API per gestione logs

### 02. Factory User Management
- **Cartella**: `02-factory-user-management/`
- **Pattern**: Factory Method, Abstract Factory
- **Descrizione**: Sistema di gestione utenti con factory pattern
- **Caratteristiche**:
  - User factories per diversi tipi di utenti
  - Notification factories
  - Database seeding automatizzato

### 03. Repository Pattern
- **Cartella**: `03-repository-pattern/`
- **Pattern**: Repository, Unit of Work
- **Descrizione**: Implementazione completa del Repository Pattern
- **Caratteristiche**:
  - Repository interfaces e implementazioni
  - Unit of Work per transazioni
  - Caching integrato
  - Testing con mocks

### 04. Service Layer
- **Cartella**: `04-service-layer/`
- **Pattern**: Service Layer, DTO
- **Descrizione**: Architettura a servizi con DTO
- **Caratteristiche**:
  - Business logic separata
  - Data Transfer Objects
  - Validation integrata
  - API RESTful

### 05. AI Chat System
- **Cartella**: `05-ai-chat-system/`
- **Pattern**: AI Gateway, AI Response Caching, AI Context Management
- **Descrizione**: Sistema di chat con integrazione IA
- **Caratteristiche**:
  - Integrazione OpenAI/Claude
  - Caching delle risposte
  - Gestione contesto conversazione
  - Streaming delle risposte

### 06. Event Sourcing
- **Cartella**: `06-event-sourcing/`
- **Pattern**: Event Sourcing, CQRS
- **Descrizione**: Implementazione Event Sourcing completa
- **Caratteristiche**:
  - Event store
  - Command/Query separation
  - Projection rebuilding
  - Audit trail completo

### 07. Microservices API
- **Cartella**: `07-microservices-api/`
- **Pattern**: API Gateway, Circuit Breaker, Service Discovery
- **Descrizione**: Architettura microservizi con Laravel
- **Caratteristiche**:
  - API Gateway
  - Service mesh
  - Circuit breaker pattern
  - Distributed tracing

### 08. CQRS Implementation
- **Cartella**: `08-cqrs-implementation/`
- **Pattern**: CQRS, Event Sourcing, Saga
- **Descrizione**: Implementazione CQRS completa
- **Caratteristiche**:
  - Command/Query separation
  - Event store
  - Saga orchestration
  - Read model optimization

## 🚀 Come Utilizzare gli Esempi

1. **Clona l'esempio** nella cartella desiderata
2. **Installa le dipendenze** con `composer install`
3. **Configura l'ambiente** copiando `.env.example` in `.env`
4. **Esegui le migrazioni** con `php artisan migrate`
5. **Avvia il server** con `php artisan serve`

## 📚 Struttura di Ogni Esempio

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

## 🔗 Link Utili
- [Indice Principale](../README.md)
- [Pattern Creazionali](../01-pattern-creazionali/)
- [Pattern Laravel-Specifici](../05-pattern-laravel-specifici/)
