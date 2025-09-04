# Factory Method Pattern

## Cosa fa
Il Factory Method ti permette di creare oggetti senza sapere esattamente quale tipo di oggetto stai creando. Definisce un'interfaccia per creare oggetti, ma lascia alle sottoclassi decidere quale classe specifica istanziare.

È come avere un'azienda che produce automobili: l'azienda sa come produrre auto in generale, ma ogni stabilimento decide se produrre SUV, berline o sportive.

## Indice
- [Cosa fa](#cosa-fa)
- [Perché ti serve](#perché-ti-serve)
- [Come funziona](#come-funziona)
- [Schema visivo](#schema-visivo)
- [Quando usarlo](#quando-usarlo)
- [Pro e contro](#pro-e-contro)
- [Esempi di codice](#esempi-di-codice)
- [Esempi completi](#esempi-completi)
- [Pattern correlati](#pattern-correlati)
- [Risorse utili](#risorse-utili)

## Perché ti serve
Immagina di dover creare diversi tipi di documenti (PDF, Word, Excel) nel tuo sistema. Senza Factory Method, finiresti con:

- Codice che conosce troppi dettagli di ogni tipo di documento
- Logica di creazione sparsa ovunque
- Difficoltà ad aggiungere nuovi tipi di documenti
- Violazione del principio "aperto per estensione, chiuso per modifica"

Il Factory Method risolve questo: una classe base sa come creare documenti in generale, e le sottoclassi decidono quale tipo specifico creare.

## Come funziona
Il meccanismo è semplice:
1. **Creator astratto**: Definisce il metodo factory ma non implementa la creazione
2. **ConcreteCreator**: Implementa il factory method per creare oggetti specifici
3. **Product**: Interfaccia per gli oggetti che vengono creati
4. **ConcreteProduct**: Implementazione concreta dell'oggetto

Il client usa solo il Creator, senza sapere quale ConcreteProduct viene effettivamente creato.

## Schema visivo
```
Scenario 1 (PDF Creator):
Client → PDFCreator → createDocument() → PDFDocument
                        ↓
                   Restituisce PDF

Scenario 2 (Word Creator):
Client → WordCreator → createDocument() → WordDocument
                        ↓
                   Restituisce Word
```

*Il diagramma mostra come lo stesso client può usare diversi creator per ottenere prodotti diversi, senza sapere quale tipo specifico viene creato.*

## Quando usarlo
Usa il Factory Method quando:
- Devi creare oggetti basandoti su configurazione o input dell'utente
- Gestisci diversi formati di file (PDF, DOC, TXT)
- Crei connessioni a database diversi
- Hai un sistema di notifiche (email, SMS, push)
- Gestisci diversi tipi di utenti o ruoli

**NON usarlo quando:**
- La creazione è semplice e non cambierà mai
- Hai solo un tipo di prodotto
- L'overhead del pattern non è giustificato

## Pro e contro
**I vantaggi:**
- Elimina l'accoppiamento tra client e classi concrete
- Facilita l'aggiunta di nuovi tipi di prodotti
- Centralizza la logica di creazione
- Rispetta il principio Open/Closed
- Migliora la testabilità

**Gli svantaggi:**
- Aumenta la complessità del codice
- Richiede più classi e interfacce
- Può essere eccessivo per creazioni semplici
- Può creare gerarchie di classi complesse

## Esempi di codice

### Esempio base
```php
<?php

// Product
interface Document
{
    public function create(): string;
}

// ConcreteProduct
class PDFDocument implements Document
{
    public function create(): string
    {
        return "PDF document created";
    }
}

class WordDocument implements Document
{
    public function create(): string
    {
        return "Word document created";
    }
}

// Creator
abstract class DocumentCreator
{
    abstract protected function createDocument(): Document;
    
    public function generateDocument(): string
    {
        $document = $this->createDocument();
        return $document->create();
    }
}

// ConcreteCreator
class PDFCreator extends DocumentCreator
{
    protected function createDocument(): Document
    {
        return new PDFDocument();
    }
}

class WordCreator extends DocumentCreator
{
    protected function createDocument(): Document
    {
        return new WordDocument();
    }
}

// Utilizzo
$pdfCreator = new PDFCreator();
echo $pdfCreator->generateDocument(); // "PDF document created"
```

### Esempio per Laravel
```php
<?php

namespace App\Services\Notification;

interface NotificationChannel
{
    public function send(string $message, string $recipient): bool;
}

class EmailChannel implements NotificationChannel
{
    public function send(string $message, string $recipient): bool
    {
        // Logica invio email
        return true;
    }
}

class SMSChannel implements NotificationChannel
{
    public function send(string $message, string $recipient): bool
    {
        // Logica invio SMS
        return true;
    }
}

abstract class NotificationFactory
{
    abstract protected function createChannel(): NotificationChannel;
    
    public function sendNotification(string $message, string $recipient): bool
    {
        $channel = $this->createChannel();
        return $channel->send($message, $recipient);
    }
}

class EmailNotificationFactory extends NotificationFactory
{
    protected function createChannel(): NotificationChannel
    {
        return new EmailChannel();
    }
}

class SMSNotificationFactory extends NotificationFactory
{
    protected function createChannel(): NotificationChannel
    {
        return new SMSChannel();
    }
}

// Utilizzo in Controller
$emailFactory = new EmailNotificationFactory();
$emailFactory->sendNotification('Welcome!', 'user@example.com');
```

## Esempi completi

Se vuoi vedere un esempio completo e funzionante, guarda:

- **[Gestione Utenti con Factory](../../../esempi-completi/02-factory-user-management/)** - Sistema di gestione utenti con factory per diversi tipi di utenti e ruoli

L'esempio include:
- Factory per creare utenti (Admin, User, Guest)
- Gestione ruoli e permessi
- Integrazione con Eloquent ORM
- Service Provider per registrare le factory
- Controller con dependency injection
- Test unitari per i factory methods
- API RESTful per gestire gli utenti

## Pattern correlati
- **Abstract Factory**: Se hai bisogno di creare famiglie di oggetti correlati
- **Builder**: Per costruire oggetti complessi passo dopo passo
- **Prototype**: Per clonare oggetti esistenti invece di crearli da zero
- **Simple Factory**: Versione semplificata senza ereditarietà

## Risorse utili
- [GoF Design Patterns](https://en.wikipedia.org/wiki/Design_Patterns) - Il libro originale dei Gang of Four
- [Refactoring.Guru - Factory Method](https://refactoring.guru/design-patterns/factory-method) - Spiegazione visuale con esempi
- [Laravel Model Factories](https://laravel.com/docs/eloquent-factories) - Come Laravel usa le factory
- [Factory Pattern in PHP](https://www.php.net/manual/en/language.oop5.patterns.php) - Documentazione ufficiale PHP
