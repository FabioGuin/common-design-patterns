# Template Method Pattern

## Cosa fa

Il Template Method Pattern definisce lo scheletro di un algoritmo in una classe base, permettendo alle sottoclassi di ridefinire alcuni passi dell'algoritmo senza cambiarne la struttura. È come avere una "ricetta" base dove alcuni ingredienti possono essere personalizzati.

## Perché ti serve

Immagina di avere diversi tipi di report (PDF, HTML, Excel) che seguono tutti lo stesso processo:
1. Raccogliere i dati
2. Formattare i dati
3. Generare l'header
4. Generare il body
5. Generare il footer
6. Salvare il file

Senza il Template Method Pattern, duplicheresti la logica in ogni classe. Con il Template Method:
- **Definisci** l'algoritmo una volta nella classe base
- **Personalizzi** solo i passi specifici nelle sottoclassi
- **Mantieni** la struttura dell'algoritmo consistente
- **Riduci** la duplicazione del codice

## Come funziona

Il pattern ha due componenti principali:

1. **AbstractClass**: Definisce il template method e i metodi astratti
2. **ConcreteClass**: Implementa i metodi astratti specifici

## Schema visivo

```
AbstractClass
    ↓
templateMethod() {
    step1() → abstract
    step2() → abstract
    step3() → concrete
}
    ↓
ConcreteClassA
ConcreteClassB
```

## Quando usarlo

- **Algoritmi** con struttura fissa ma passi variabili
- **Frameworks** che definiscono il flusso
- **Code generation** tools
- **Data processing** pipelines
- **Laravel Eloquent** (per lifecycle hooks)
- **Laravel Jobs** (per il flusso di esecuzione)

## Pro e contro

### Pro
- **Code reuse**: Evita duplicazione del codice
- **Consistent structure**: Struttura consistente dell'algoritmo
- **Easy to extend**: Facile aggiungere nuove implementazioni
- **Control flow**: Controllo del flusso nella classe base

### Contro
- **Inheritance**: Richiede ereditarietà
- **Rigid structure**: La struttura è fissa
- **Debugging**: Può essere difficile debuggare
- **LSP violation**: Può violare il principio di sostituzione di Liskov

## Esempi di codice

### Classe astratta
```pseudocodice
abstract class ReportGenerator {
    // Template method - definisce la struttura
    final function generateReport(data: array): string {
        this.validateData(data)
        formattedData = this.formatData(data)
        header = this.generateHeader()
        body = this.generateBody(formattedData)
        footer = this.generateFooter()
        
        return this.combineSections(header, body, footer)
    }
    
    // Metodi astratti - devono essere implementati dalle sottoclassi
    abstract protected function formatData(data: array): array
    abstract protected function generateHeader(): string
    abstract protected function generateBody(data: array): string
    abstract protected function generateFooter(): string
    
    // Metodi concreti - implementazione comune
    protected function validateData(data: array): void {
        if (empty(data)) {
            throw new InvalidArgumentException('Data cannot be empty')
        }
    }
    
    protected function combineSections(header: string, body: string, footer: string): string {
        return header + "\n" + body + "\n" + footer
    }
}
```

### Implementazioni concrete
```pseudocodice
class PDFReportGenerator extends ReportGenerator {
    protected function formatData(data: array): array {
        // Formatta i dati per PDF
        return array_map(function(item) {
            return [
                'title' => strtoupper(item['title']),
                'content' => wordwrap(item['content'], 80)
            ]
        }, data)
    }
    
    protected function generateHeader(): string {
        return "=== PDF REPORT ===\n" + date('Y-m-d H:i:s')
    }
    
    protected function generateBody(data: array): string {
        body = ""
        foreach (data as item) {
            body += "Title: " + item['title'] + "\n"
            body += "Content: " + item['content'] + "\n\n"
        }
        return body
    }
    
    protected function generateFooter(): string {
        return "=== END OF PDF REPORT ==="
    }
}

class HTMLReportGenerator extends ReportGenerator {
    protected function formatData(data: array): array {
        // Formatta i dati per HTML
        return array_map(function(item) {
            return [
                'title' => htmlspecialchars(item['title']),
                'content' => nl2br(htmlspecialchars(item['content']))
            ]
        }, data)
    }
    
    protected function generateHeader(): string {
        return "<h1>HTML Report</h1><p>Generated: " + date('Y-m-d H:i:s') + "</p>"
    }
    
    protected function generateBody(data: array): string {
        body = "<div class='report-body'>"
        foreach (data as item) {
            body += "<h2>" + item['title'] + "</h2>"
            body += "<p>" + item['content'] + "</p>"
        }
        body += "</div>"
        return body
    }
    
    protected function generateFooter(): string {
        return "<footer>End of HTML Report</footer>"
    }
}
```

### Uso
```pseudocodice
data = [
    ['title' => 'Report 1', 'content' => 'This is the first report'],
    ['title' => 'Report 2', 'content' => 'This is the second report']
]

// Genera report PDF
pdfGenerator = new PDFReportGenerator()
pdfReport = pdfGenerator.generateReport(data)

// Genera report HTML
htmlGenerator = new HTMLReportGenerator()
htmlReport = htmlGenerator.generateReport(data)
```

## Esempi completi

Vedi la cartella `esempio-completo` per un'implementazione completa in Laravel che mostra:
- Sistema di generazione report
- Pipeline di processing dati
- Sistema di notifiche
- Framework per API

## Correlati

- **Strategy Pattern**: Per algoritmi intercambiabili
- **Factory Method Pattern**: Per creare oggetti
- **Command Pattern**: Per incapsulare operazioni

## Esempi di uso reale

- **Laravel Eloquent**: Lifecycle hooks (creating, created, updating, updated)
- **Laravel Jobs**: Flusso di esecuzione dei job
- **Laravel Mail**: Template per email
- **Laravel Validation**: Regole di validazione
- **Code generators**: Per generare codice
- **Data processors**: Per processing di dati

## Anti-pattern

❌ **Template troppo rigido**: Un template che non permette flessibilità
```pseudocodice
// SBAGLIATO
abstract class RigidTemplate {
    final function process(): void {
        this.step1() // Sempre obbligatorio
        this.step2() // Sempre obbligatorio
        this.step3() // Sempre obbligatorio
        // Nessuna flessibilità!
    }
}
```

✅ **Template flessibile**: Un template che permette personalizzazioni
```pseudocodice
// GIUSTO
abstract class FlexibleTemplate {
    final function process(): void {
        this.step1()
        if (this.shouldExecuteStep2()) {
            this.step2()
        }
        this.step3()
    }
    
    protected function shouldExecuteStep2(): Boolean {
        return true // Default, può essere sovrascritto
    }
}
```

## Troubleshooting

**Problema**: Metodo astratto non implementato
**Soluzione**: Assicurati che tutte le sottoclassi implementino i metodi astratti

**Problema**: Template troppo rigido
**Soluzione**: Aggiungi hook methods per maggiore flessibilità

**Problema**: Difficile testare
**Soluzione**: Crea classi di test che estendono la classe astratta

## Performance e considerazioni

- **Method calls**: Considera il costo delle chiamate ai metodi
- **Memory usage**: Ogni sottoclasse è un oggetto
- **Caching**: Per risultati costosi da calcolare
- **Validation**: Per validare i dati prima del processing

## Risorse utili

- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Laravel Jobs](https://laravel.com/docs/queues)
- [Laravel Mail](https://laravel.com/docs/mail)
- [Template Method Pattern su Refactoring.Guru](https://refactoring.guru/design-patterns/template-method)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
