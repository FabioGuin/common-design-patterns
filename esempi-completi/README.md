# Esempi Completi

## Cosa trovi qui
Questa cartella contiene progetti Laravel completi e funzionanti che mostrano come usare i pattern di design nella pratica. Ogni esempio è un progetto vero che puoi scaricare e usare.

## Perché sono utili
- Codice funzionante che puoi copiare e incollare
- Esempi reali di come integrare i pattern in Laravel
- Riferimento per i tuoi progetti futuri

## Struttura degli esempi

Gli esempi sono ora organizzati seguendo la stessa struttura della documentazione: `[argomento][pattern]/esempio-completo/`

### Pattern Creazionali

#### Singleton Pattern
- **Percorso**: `../01-pattern-creazionali/01-singleton/esempio-completo/`
- **Pattern**: Singleton
- **Cosa fa**: Un sistema di logging che usa una sola istanza per tutta l'app
- **Cosa include**: 
  - Logger service singleton funzionante
  - Salvataggio dei log su file
  - Integrazione con Laravel Service Container
  - API per gestire i log

#### Factory Method Pattern
- **Percorso**: `../01-pattern-creazionali/02-factory-method/esempio-completo/`
- **Pattern**: Factory Method
- **Cosa fa**: Sistema per creare diversi tipi di utenti usando le factory
- **Cosa include**:
  - Factory per diversi tipi di utenti
  - Factory per le notifiche
  - Seeding automatico del database

#### Abstract Factory Pattern
- **Percorso**: `../01-pattern-creazionali/03-abstract-factory/esempio-completo/`
- **Pattern**: Abstract Factory
- **Cosa fa**: Sistema di pagamento che funziona con Stripe, PayPal e altri
- **Cosa include**:
  - Gruppi di classi che vanno insieme (Stripe, PayPal)
  - Gateway, Validator e Logger compatibili
  - Configurazione dinamica dei provider
  - API RESTful completa

#### Builder Pattern
- **Percorso**: `../01-pattern-creazionali/04-builder/esempio-completo/`
- **Pattern**: Builder
- **Cosa fa**: Sistema per costruire utenti complessi passo dopo passo
- **Cosa include**:
  - Builder per utenti con profili e ruoli
  - Fluent interface per costruzione
  - Validazione integrata
  - Test completi

#### Prototype Pattern
- **Percorso**: `../01-pattern-creazionali/05-prototype/esempio-completo/`
- **Pattern**: Prototype
- **Cosa fa**: Sistema per clonare documenti e template
- **Cosa include**:
  - Clonazione profonda di oggetti complessi
  - Gestione versioni
  - Template system
  - API per documenti

#### Object Pool Pattern
- **Percorso**: `../01-pattern-creazionali/06-object-pool/esempio-completo/`
- **Pattern**: Object Pool
- **Cosa fa**: Sistema di gestione connessioni database con pool
- **Cosa include**:
  - Pool di connessioni PDO
  - Gestione automatica del ciclo di vita
  - Monitoraggio delle performance
  - API RESTful per monitoraggio

## Come usare gli esempi

1. **Naviga alla cartella del pattern** che ti interessa (es. `01-pattern-creazionali/01-singleton/`)
2. **Entra nella cartella esempio-completo** per vedere il progetto
3. **Installa le dipendenze** con `composer install`
4. **Configura l'ambiente** copiando `.env.example` in `.env`
5. **Esegui le migrazioni** con `php artisan migrate`
6. **Avvia il server** con `php artisan serve`

## Struttura di ogni esempio

```
[argomento][pattern]/esempio-completo/
├── README.md                 # Documentazione specifica
├── app/                      # Codice applicazione
├── config/                   # Configurazioni
├── database/                 # Migrazioni e seeders
├── routes/                   # Definizione routes
├── tests/                    # Test suite
├── composer.json             # Dipendenze
└── .env.example              # Configurazione ambiente
```

## Vantaggi della nuova struttura

- **Coerenza**: Gli esempi seguono la stessa struttura della documentazione
- **Facilità di navigazione**: Trovi subito l'esempio del pattern che stai studiando
- **Organizzazione logica**: Pattern raggruppati per categoria
- **Scalabilità**: Facile aggiungere nuovi esempi nella categoria giusta

## Link utili
- [Torna all'indice principale](../README.md)
- [Pattern Creazionali](../01-pattern-creazionali/)
- [Pattern Laravel-Specifici](../05-pattern-laravel-specifici/)
