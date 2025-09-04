# Document Prototype System - Esempio Completo

## Cosa fa questo esempio
Questo esempio dimostra l'implementazione del **Prototype Pattern** per clonare documenti e template in Laravel. Il sistema permette di creare copie di documenti complessi con metadati, impostazioni e contenuti in modo efficiente.

## Caratteristiche principali
- **Document Cloning**: Clonazione profonda di documenti complessi
- **Template System**: Sistema di template riutilizzabili
- **Metadata Management**: Gestione di metadati e impostazioni
- **Version Control**: Sistema di versioning per documenti
- **Test**: Test completi con Pest
- **API**: Endpoint REST per dimostrare l'uso

## Struttura del progetto
```
app/
├── Models/
│   ├── Document.php              # Modello Document con clonazione
│   ├── DocumentTemplate.php      # Modello Template
│   ├── DocumentVersion.php       # Modello Version
│   └── DocumentMetadata.php      # Modello Metadata
├── Services/
│   ├── DocumentCloningService.php # Service per clonazione
│   └── TemplateService.php       # Service per template
├── Http/
│   └── Controllers/
│       └── DocumentController.php # Controller per API
└── Traits/
    └── Cloneable.php             # Trait per clonazione

database/
├── migrations/
│   ├── create_documents_table.php
│   ├── create_document_templates_table.php
│   ├── create_document_versions_table.php
│   └── create_document_metadata_table.php
└── seeders/
    └── DocumentSeeder.php

tests/
└── Feature/
    └── DocumentPrototypeTest.php # Test completi

routes/
└── api.php                       # Route API
```

## Come usarlo

### 1. Installazione
```bash
composer install
php artisan migrate
php artisan db:seed
```

### 2. Esempi di uso

#### Clonazione semplice
```php
$originalDocument = Document::find(1);
$clonedDocument = $originalDocument->clone('Nuovo Documento');
```

#### Clonazione con modifiche
```php
$template = DocumentTemplate::find(1);
$document = $template->createDocument('Mio Documento', [
    'content' => 'Contenuto personalizzato',
    'author' => 'Mario Rossi'
]);
```

#### Clonazione profonda
```php
$service = new DocumentCloningService();
$clonedDocument = $service->deepClone($originalDocument, [
    'title' => 'Copia di ' . $originalDocument->title,
    'status' => 'draft'
]);
```

### 3. API Endpoints
- `POST /api/documents/{id}/clone` - Clona un documento
- `POST /api/templates/{id}/create-document` - Crea documento da template
- `GET /api/documents` - Lista documenti
- `GET /api/templates` - Lista template
- `POST /api/documents` - Crea nuovo documento
- `PUT /api/documents/{id}` - Aggiorna documento

### 4. Test
```bash
php artisan test
```

## Vantaggi del Prototype Pattern
- **Performance**: Clonazione veloce di oggetti complessi
- **Flessibilità**: Modifica solo i campi necessari
- **Consistenza**: Mantiene la struttura originale
- **Efficienza**: Evita di ricostruire oggetti complessi
- **Versioning**: Facile creazione di versioni

## Pattern correlati
- **Factory Method**: Per creare diversi tipi di documenti
- **Builder**: Per costruire documenti complessi
- **Template Method**: Per definire il processo di clonazione
