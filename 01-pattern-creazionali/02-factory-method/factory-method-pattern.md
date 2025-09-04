# Factory Method Pattern
*(Categoria: Creazionale)*

## Indice
- [Abstract](#abstract)
- [Contesto e Motivazione](#contesto-e-motivazione)
- [Soluzione proposta](#soluzione-proposta)
- [Quando usarlo](#quando-usarlo)
- [Vantaggi e Svantaggi](#vantaggi-e-svantaggi)
- [Esempi pratici](#esempi-pratici)
  - [Esempio concettuale](#esempio-concettuale)
  - [Esempio Laravel](#esempio-laravel)
- [Esempi Completi](#esempi-completi)

## Abstract
Il Factory Method Pattern definisce un'interfaccia per creare oggetti, ma lascia alle sottoclassi decidere quale classe istanziare. Permette di delegare la creazione di oggetti alle sottoclassi, mantenendo il codice client indipendente dalle classi concrete che crea.

## Contesto e Motivazione
- **Contesto tipico**: Quando hai bisogno di creare oggetti ma non conosci esattamente quale tipo di oggetto creare fino al runtime, o quando la logica di creazione è complessa
- **Sintomi di un design non ottimale**: 
  - Codice client che conosce troppi dettagli delle classi concrete
  - Logica di creazione sparsa in tutto il codice
  - Difficoltà nell'aggiungere nuovi tipi di oggetti
  - Violazione del principio Open/Closed
- **Perché le soluzioni semplici non sono ideali**: Usare direttamente `new` con classi concrete crea accoppiamento forte e rende difficile estendere il sistema con nuovi tipi di oggetti.

## Soluzione proposta
- **Idea chiave**: Definisce un metodo factory astratto che le sottoclassi implementano per creare oggetti specifici, mantenendo il client indipendente dalle classi concrete
- **Struttura concettuale**: 
  - Creator astratto con metodo factory
  - ConcreteCreator che implementa il factory method
  - Product astratto e ConcreteProduct
  - Client che usa il Creator
- **Ruolo dei partecipanti**:
  - **Creator**: Classe astratta che definisce il factory method
  - **ConcreteCreator**: Implementa il factory method per creare prodotti specifici
  - **Product**: Interfaccia per gli oggetti creati dal factory
  - **ConcreteProduct**: Implementazione concreta del prodotto

## Quando usarlo
- **Casi d'uso ideali**:
  - Creazione di oggetti basata su configurazione o input utente
  - Gestione di diversi formati di file (PDF, DOC, TXT)
  - Creazione di connessioni a database diversi
  - Sistema di notifiche (email, SMS, push)
  - Gestione di diversi tipi di utenti o ruoli
- **Indicatori che suggeriscono l'adozione**:
  - Necessità di creare oggetti senza conoscere la classe esatta
  - Logica di creazione complessa o condizionale
  - Estensibilità futura con nuovi tipi di oggetti
- **Situazioni in cui NON è consigliato**:
  - Quando la creazione è semplice e non cambierà
  - Se hai solo un tipo di prodotto
  - Quando l'overhead del pattern non è giustificato

## Vantaggi e Svantaggi
**Vantaggi**
- Elimina l'accoppiamento tra client e classi concrete
- Facilita l'aggiunta di nuovi tipi di prodotti
- Centralizza la logica di creazione
- Rispetta il principio Open/Closed
- Migliora la testabilità con dependency injection

**Svantaggi**
- Aumenta la complessità del codice
- Richiede più classi e interfacce
- Può essere eccessivo per creazioni semplici
- Può creare gerarchie di classi complesse

## Esempi pratici

### Esempio concettuale
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

### Esempio Laravel
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

## Esempi Completi

Per implementazioni complete e funzionanti del Factory Method Pattern in Laravel, consulta:

- **[Esempio Completo: Factory User Management](../../../esempi-completi/02-factory-user-management/)** - Sistema di gestione utenti con factory per diversi tipi di utenti e ruoli

L'esempio completo include:
- Factory per creazione utenti (Admin, User, Guest)
- Gestione ruoli e permessi
- Integrazione con Eloquent ORM
- Service Provider per registrazione factory
- Controller con dependency injection
- Test unitari per factory methods
- API RESTful per gestione utenti
