<?php

require_once 'vendor/autoload.php';

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentCloningService;

// Simula l'ambiente Laravel per il test
echo "=== ESEMPIO PROTOTYPE PATTERN ===\n\n";

echo "1. Creazione documento originale:\n";
$originalDocument = new Document([
    'title' => 'Documento Originale',
    'content' => 'Contenuto del documento originale con metadati complessi',
    'status' => 'draft',
    'metadata' => [
        'type' => 'article',
        'author' => 'Mario Rossi',
        'tags' => ['laravel', 'php', 'tutorial'],
        'settings' => ['format' => 'markdown', 'auto_save' => true]
    ],
    'settings' => [
        'format' => 'markdown',
        'auto_save' => true,
        'word_count' => true
    ],
    'tags' => ['laravel', 'php', 'tutorial'],
    'version' => 1
]);

echo "Documento creato: {$originalDocument->title}\n";
echo "Metadati: " . json_encode($originalDocument->metadata) . "\n\n";

echo "2. Clonazione semplice:\n";
$clonedDocument = $originalDocument->clone('Documento Clonato');
echo "Documento clonato: {$clonedDocument->title}\n";
echo "Contenuto identico: " . ($clonedDocument->content === $originalDocument->content ? 'Sì' : 'No') . "\n";
echo "Metadati identici: " . (json_encode($clonedDocument->metadata) === json_encode($originalDocument->metadata) ? 'Sì' : 'No') . "\n\n";

echo "3. Clonazione con dati personalizzati:\n";
$customDocument = $originalDocument->cloneWithCustomData([
    'title' => 'Documento Personalizzato',
    'status' => 'published',
    'metadata' => [
        'type' => 'custom',
        'author' => 'Giulia Bianchi',
        'tags' => ['custom', 'modified'],
        'settings' => ['format' => 'html', 'auto_save' => false]
    ]
]);

echo "Documento personalizzato: {$customDocument->title}\n";
echo "Status: {$customDocument->status}\n";
echo "Autore: {$customDocument->metadata['author']}\n";
echo "Formato: {$customDocument->metadata['settings']['format']}\n\n";

echo "4. Creazione template:\n";
$template = new DocumentTemplate([
    'name' => 'Template Articolo',
    'description' => 'Template per articoli di blog',
    'content' => '# {{title}}\n\n## Introduzione\n{{introduction}}\n\n## Contenuto principale\n{{main_content}}\n\n## Conclusione\n{{conclusion}}',
    'metadata' => [
        'type' => 'template',
        'required_fields' => ['title', 'introduction', 'main_content', 'conclusion']
    ],
    'settings' => [
        'format' => 'markdown',
        'auto_save' => true
    ],
    'tags' => ['template', 'article', 'blog'],
    'category' => 'content',
    'is_active' => true
]);

echo "Template creato: {$template->name}\n";
echo "Categoria: {$template->category}\n";
echo "Campi richiesti: " . implode(', ', $template->metadata['required_fields']) . "\n\n";

echo "5. Creazione documento da template:\n";
$documentFromTemplate = $template->createDocument('Articolo da Template', [
    'content' => '# Il Mio Articolo\n\n## Introduzione\nQuesto è un articolo creato da template.\n\n## Contenuto principale\nIl contenuto principale dell\'articolo...\n\n## Conclusione\nIn conclusione, questo è un esempio di clonazione.',
    'status' => 'published'
]);

echo "Documento da template: {$documentFromTemplate->title}\n";
echo "Status: {$documentFromDocument->status}\n";
echo "Template ID: {$documentFromTemplate->template_id}\n\n";

echo "6. Clonazione template:\n";
$clonedTemplate = $template->cloneTemplate('Template Articolo Clonato');
echo "Template clonato: {$clonedTemplate->name}\n";
echo "Attivo: " . ($clonedTemplate->is_active ? 'Sì' : 'No') . "\n";
echo "Contenuto identico: " . ($clonedTemplate->content === $template->content ? 'Sì' : 'No') . "\n\n";

echo "7. Test clonazione profonda con service:\n";
$cloningService = new DocumentCloningService();

// Simula clonazione profonda
$deepClonedDocument = $originalDocument->cloneWithCustomData([
    'title' => 'Documento Clonato Profondamente',
    'status' => 'published',
    'version' => 1
]);

echo "Documento clonato profondamente: {$deepClonedDocument->title}\n";
echo "Version: {$deepClonedDocument->version}\n";
echo "Status: {$deepClonedDocument->status}\n\n";

echo "8. Test gestione metadati:\n";
$document = $originalDocument->clone('Documento per Test Metadati');

// Simula operazioni sui metadati
$document->setMetadataValue('new_key', 'new_value');
$document->setSettingValue('new_setting', 'new_value');

echo "Documento: {$document->title}\n";
echo "Nuovo metadato: {$document->getMetadataValue('new_key')}\n";
echo "Nuova impostazione: {$document->getSettingValue('new_setting')}\n";
echo "Metadato di default: {$document->getMetadataValue('nonexistent', 'default_value')}\n\n";

echo "9. Test versioning:\n";
$versionedDocument = $originalDocument->clone('Documento con Versioni');
$versionedDocument->version = 1;

// Simula creazione versioni
echo "Documento: {$versionedDocument->title}\n";
echo "Versione corrente: {$versionedDocument->version}\n";
echo "Titolo completo: {$versionedDocument->getFullTitleAttribute()}\n\n";

echo "10. Test stati documento:\n";
$statusDocument = $originalDocument->clone('Documento per Test Stati');

echo "Documento: {$statusDocument->title}\n";
echo "È draft: " . ($statusDocument->isDraft() ? 'Sì' : 'No') . "\n";
echo "È pubblicato: " . ($statusDocument->isPublished() ? 'Sì' : 'No') . "\n";
echo "È archiviato: " . ($statusDocument->isArchived() ? 'Sì' : 'No') . "\n\n";

echo "=== FINE ESEMPIO ===\n";
