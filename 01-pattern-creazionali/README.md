# Pattern Creazionali (Creational Patterns)

## 📝 Descrizione
I pattern creazionali forniscono vari meccanismi per creare oggetti, aumentando la flessibilità e riutilizzabilità del codice esistente.

## 🎯 Obiettivo
- Incapsulare la logica di creazione degli oggetti
- Rendere il sistema indipendente da come gli oggetti vengono creati, composti e rappresentati
- Fornire flessibilità nella creazione di oggetti complessi

## 📋 Pattern Inclusi

### 1.1 Singleton
- **File**: `01-singleton/singleton-pattern.md`
- **Descrizione**: Garantisce una sola istanza di una classe
- **Utilizzo Laravel**: Service Container, Database connections, Cache

### 1.2 Factory Method
- **File**: `02-factory-method/`
- **Descrizione**: Delega la creazione di oggetti a sottoclassi
- **Utilizzo Laravel**: Model factories, Service providers

### 1.3 Abstract Factory
- **File**: `03-abstract-factory/`
- **Descrizione**: Crea famiglie di oggetti correlati
- **Utilizzo Laravel**: Payment gateways, Notification channels

### 1.4 Builder
- **File**: `04-builder/`
- **Descrizione**: Costruisce oggetti complessi step-by-step
- **Utilizzo Laravel**: Query Builder, Eloquent, Email building

### 1.5 Prototype
- **File**: `05-prototype/`
- **Descrizione**: Crea oggetti clonando un prototipo
- **Utilizzo Laravel**: Template system, Document cloning

### 1.6 Object Pool
- **File**: `06-object-pool/`
- **Descrizione**: Riutilizza oggetti costosi
- **Utilizzo Laravel**: Connection pooling, Cache pools

## 🔗 Link Utili
- [Indice Principale](../../index.md)
- [Esempi Completi](../../esempi-completi/)
