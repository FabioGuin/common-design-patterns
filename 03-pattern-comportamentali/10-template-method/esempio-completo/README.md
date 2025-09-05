# Esempio Completo: Template Method Pattern

Questo esempio dimostra l'implementazione del **Template Method Pattern** in Laravel per definire scheletri di algoritmi.

## Funzionalità implementate

- **Sistema di generazione report** (PDF, HTML, Excel)
- **Pipeline di processing** dati
- **Sistema di notifiche**
- **Framework per API**

## Struttura del progetto

```
esempio-completo/
├── app/
│   ├── Http/Controllers/
│   │   └── ReportController.php
│   └── Services/
│       ├── Templates/
│       │   ├── ReportGenerator.php
│       │   ├── PDFReportGenerator.php
│       │   ├── HTMLReportGenerator.php
│       │   └── ExcelReportGenerator.php
│       └── Processors/
│           ├── DataProcessor.php
│           └── NotificationProcessor.php
├── resources/views/
│   └── reports/
│       └── index.blade.php
├── routes/
│   └── web.php
└── composer.json
```

## Esempi di utilizzo

### Generazione Report
```php
$pdfGenerator = new PDFReportGenerator();
$pdfReport = $pdfGenerator->generateReport($data);

$htmlGenerator = new HTMLReportGenerator();
$htmlReport = $htmlGenerator->generateReport($data);
```

## Pattern implementati

- **Template Method Pattern**: Scheletri di algoritmi
- **Abstract Class Pattern**: Per classi base
- **Hook Method Pattern**: Per personalizzazioni
