# Event System Pattern

## Indice

### Comprensione Base
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)

### Valutazione e Contesto
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Correlati](#correlati)
- [Esempi di uso reale](#esempi-di-uso-reale)

### Cosa Evitare
- [Anti-pattern](#anti-pattern)
- [Troubleshooting](#troubleshooting)

### Implementazione Pratica
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)

### Considerazioni Tecniche
- [Performance e considerazioni](#performance-e-considerazioni)
- [Risorse utili](#risorse-utili)

## Cosa fa

L'Event System Pattern permette di disaccoppiare i componenti di un'applicazione attraverso un sistema di eventi. Quando succede qualcosa di importante (un utente si registra, un ordine viene completato, un file viene caricato), l'applicazione "emette" un evento che altri componenti possono "ascoltare" e reagire di conseguenza.

Pensa al sistema di eventi come a un sistema di notifiche: quando qualcosa succede, viene inviata una notifica a tutti coloro che sono interessati, senza che chi emette l'evento debba sapere chi lo riceverà.

## Perché ti serve

Senza eventi, i componenti sono strettamente accoppiati. Se vuoi inviare un'email quando un utente si registra, devi modificare il codice di registrazione. Se vuoi aggiungere un log, devi modificare di nuovo lo stesso codice. Risultato? Codice fragile e difficile da mantenere.

Con l'Event System ottieni:
- **Disaccoppiamento**: I componenti non dipendono direttamente l'uno dall'altro
- **Estensibilità**: Aggiungi nuove funzionalità senza modificare il codice esistente
- **Manutenibilità**: Ogni listener ha una responsabilità specifica
- **Testabilità**: Puoi testare ogni listener isolatamente
- **Flessibilità**: Attiva/disattiva funzionalità facilmente
- **Scalabilità**: Aggiungi listener per gestire carichi diversi

## Come funziona

1. **Evento emesso**: Qualcosa succede nell'applicazione
2. **Dispatcher riceve**: Il sistema di eventi riceve l'evento
3. **Listener notificati**: Tutti i listener registrati per quell'evento vengono eseguiti
4. **Elaborazione asincrona**: I listener possono essere eseguiti in background
5. **Gestione errori**: Se un listener fallisce, gli altri continuano

## Schema visivo

```
Flusso Eventi:
[Componente A] → [Evento] → [Dispatcher] → [Listener 1]
                                    ↓
                              [Listener 2]
                                    ↓
                              [Listener 3]

Esempio concreto:
[UserController] → [UserRegistered] → [EventDispatcher] → [SendWelcomeEmail]
                                                      → [LogRegistration]
                                                      → [UpdateAnalytics]
```

## Quando usarlo

Usa l'Event System quando:
- Hai bisogno di disaccoppiare componenti
- Vuoi aggiungere funzionalità senza modificare il codice esistente
- Hai operazioni che possono essere eseguite in background
- Vuoi notificare più componenti di un cambiamento
- Hai bisogno di logging o auditing
- Vuoi implementare un sistema di notifiche

**NON usarlo quando:**
- Hai solo un listener per evento
- L'operazione deve essere sincrona e critica
- Hai bisogno di una risposta immediata
- Il sistema è troppo semplice per giustificare la complessità

## Pro e contro

**I vantaggi:**
- Disaccoppiamento completo tra componenti
- Facile aggiunta di nuove funzionalità
- Testabilità migliorata
- Elaborazione asincrona
- Scalabilità orizzontale

**Gli svantaggi:**
- Complessità aggiuntiva
- Debugging più difficile
- Overhead di performance
- Gestione degli errori complessa
- Possibili race conditions

## Esempi di codice

### Pseudocodice
```pseudocodice
// Definizione Evento
class UserRegistered {
    constructor(user, timestamp) {
        this.user = user
        this.timestamp = timestamp
    }
}

// Listener per invio email
class SendWelcomeEmail {
    handle(event) {
        email = new EmailService()
        email.send(event.user.email, 'Benvenuto!')
    }
}

// Listener per logging
class LogRegistration {
    handle(event) {
        logger.info('User registered: ' + event.user.email)
    }
}

// Registrazione listener
eventDispatcher.listen('UserRegistered', SendWelcomeEmail)
eventDispatcher.listen('UserRegistered', LogRegistration)

// Emissione evento
eventDispatcher.dispatch(new UserRegistered(user, now()))
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Event System Completo](./esempio-completo/)** - Sistema completo di eventi con listener, queue e gestione errori

L'esempio include:
- Eventi personalizzati
- Listener sincroni e asincroni
- Gestione delle code
- Error handling
- Testing degli eventi
- Monitoring e logging

**Nota per l'implementazione**: L'esempio completo segue il [template semplificato](../TEMPLATE-ESEMPIO-COMPLETO.md) con focus sulla dimostrazione del pattern, non su un'applicazione completa.

## Correlati

### Pattern

- **[Observer Pattern](../03-pattern-comportamentali/07-observer/observer-pattern.md)** - Pattern base per notifiche
- **[Command Pattern](../03-pattern-comportamentali/02-command/command-pattern.md)** - Esecuzione di comandi
- **[Mediator Pattern](../03-pattern-comportamentali/05-mediator/mediator-pattern.md)** - Comunicazione tra componenti
- **[Service Container Pattern](./01-service-container/service-container-pattern.md)** - Gestione delle dipendenze

### Principi e Metodologie

- **[DRY Pattern](../00-fondamentali/01-dry-pattern/dry-pattern.md)** - Evita duplicazione del codice
- **[SOLID Principles](../00-fondamentali/04-solid-principles/solid-principles.md)** - Principi fondamentali di design
- **[Clean Code](../00-fondamentali/05-clean-code/clean-code.md)** - Scrittura di codice pulito
- **[TDD](../00-fondamentali/09-tdd/tdd.md)** - Test-Driven Development

## Esempi di uso reale

- **E-commerce**: Notifiche per ordini, pagamenti, spedizioni
- **Social Media**: Notifiche per like, commenti, condivisioni
- **Sistemi di Logging**: Eventi di sistema, errori, performance
- **Workflow**: Approvazioni, notifiche, processi aziendali
- **Analytics**: Tracking di eventi utente, conversioni, metriche

## Anti-pattern

**Cosa NON fare:**
- Usare eventi per operazioni critiche che devono essere sincrone
- Creare troppi eventi per azioni semplici
- Non gestire gli errori nei listener
- Usare eventi per comunicazione bidirezionale
- Non documentare gli eventi e i loro listener

## Troubleshooting

### Problemi comuni
- **Evento non ricevuto**: Verifica la registrazione del listener
- **Listener non eseguito**: Controlla la coda e i worker
- **Errori silenziosi**: Implementa logging e monitoring
- **Performance lente**: Ottimizza i listener e usa code asincrone

### Debug e monitoring
- Usa logging per tracciare eventi e listener
- Monitora le code per evitare accumuli
- Implementa metriche per performance
- Testa i listener isolatamente

## Performance e considerazioni

### Impatto sulle risorse
- **Memoria**: Ogni evento e listener occupa memoria
- **CPU**: Elaborazione asincrona riduce il carico immediato
- **I/O**: Listener possono fare molte operazioni di database/API

### Scalabilità
- **Carico basso**: Eventi sincroni vanno bene
- **Carico medio**: Usa code asincrone
- **Carico alto**: Distribuisci i listener su più server

### Colli di bottiglia
- **Queue processing**: I worker possono diventare un collo di bottiglia
- **Database**: Troppi listener che scrivono sul database
- **Network**: Listener che fanno chiamate API esterne

## Risorse utili

### Documentazione ufficiale
- [Laravel Events](https://laravel.com/docs/events) - Sistema eventi Laravel
- [Laravel Queues](https://laravel.com/docs/queues) - Elaborazione asincrona
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale

### Esempi e tutorial
- [Laravel Event Broadcasting](https://laravel.com/docs/broadcasting) - Eventi real-time
- [Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html) - Pattern avanzato
- [CQRS](https://martinfowler.com/bliki/CQRS.html) - Command Query Responsibility Segregation

### Strumenti di supporto
- [Laravel Horizon](https://laravel.com/docs/horizon) - Dashboard per code
- [Laravel Telescope](https://laravel.com/docs/telescope) - Debug e profiling
- [Checklist di Implementazione](../00-fondamentali/checklist-implementazione-pattern.md) - Guida step-by-step
