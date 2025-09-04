<?php

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Services\DocumentCloningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
});

test('può clonare un documento semplice', function () {
    $originalDocument = Document::create([
        'title' => 'Documento Originale',
        'content' => 'Contenuto del documento originale',
        'status' => 'draft',
        'metadata' => ['type' => 'test'],
        'settings' => ['format' => 'markdown'],
        'tags' => ['test', 'clone'],
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    $clonedDocument = $originalDocument->clone('Documento Clonato');

    expect($clonedDocument->title)->toBe('Documento Clonato');
    expect($clonedDocument->content)->toBe('Contenuto del documento originale');
    expect($clonedDocument->metadata)->toBe(['type' => 'test']);
    expect($clonedDocument->settings)->toBe(['format' => 'markdown']);
    expect($clonedDocument->tags)->toBe(['test', 'clone']);
    expect($clonedDocument->status)->toBe('draft');
    expect($clonedDocument->version)->toBe(1);
    expect($clonedDocument->id)->not->toBe($originalDocument->id);
});

test('può clonare un documento con dati personalizzati', function () {
    $originalDocument = Document::create([
        'title' => 'Documento Originale',
        'content' => 'Contenuto del documento originale',
        'status' => 'draft',
        'metadata' => ['type' => 'test'],
        'settings' => ['format' => 'markdown'],
        'tags' => ['test', 'clone'],
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    $clonedDocument = $originalDocument->cloneWithCustomData([
        'title' => 'Documento Personalizzato',
        'status' => 'published',
        'metadata' => ['type' => 'custom', 'author' => 'Test Author']
    ]);

    expect($clonedDocument->title)->toBe('Documento Personalizzato');
    expect($clonedDocument->status)->toBe('published');
    expect($clonedDocument->metadata)->toBe(['type' => 'custom', 'author' => 'Test Author']);
    expect($clonedDocument->content)->toBe('Contenuto del documento originale');
});

test('può creare un documento da template', function () {
    $template = DocumentTemplate::create([
        'name' => 'Test Template',
        'description' => 'Template di test',
        'content' => 'Contenuto del template: {{title}}',
        'metadata' => ['type' => 'template'],
        'settings' => ['format' => 'markdown'],
        'tags' => ['template', 'test'],
        'category' => 'test',
        'is_active' => true,
        'created_by' => $this->user->id
    ]);

    $document = $template->createDocument('Documento da Template', [
        'content' => 'Contenuto personalizzato',
        'status' => 'published'
    ]);

    expect($document->title)->toBe('Documento da Template');
    expect($document->content)->toBe('Contenuto personalizzato');
    expect($document->template_id)->toBe($template->id);
    expect($document->status)->toBe('published');
    expect($document->metadata)->toBe(['type' => 'template']);
    expect($document->settings)->toBe(['format' => 'markdown']);
});

test('può clonare un template', function () {
    $originalTemplate = DocumentTemplate::create([
        'name' => 'Template Originale',
        'description' => 'Template originale',
        'content' => 'Contenuto del template originale',
        'metadata' => ['type' => 'template'],
        'settings' => ['format' => 'markdown'],
        'tags' => ['template', 'original'],
        'category' => 'test',
        'is_active' => true,
        'created_by' => $this->user->id
    ]);

    $clonedTemplate = $originalTemplate->cloneTemplate('Template Clonato');

    expect($clonedTemplate->name)->toBe('Template Clonato');
    expect($clonedTemplate->content)->toBe('Contenuto del template originale');
    expect($clonedTemplate->metadata)->toBe(['type' => 'template']);
    expect($clonedTemplate->settings)->toBe(['format' => 'markdown']);
    expect($clonedTemplate->is_active)->toBeFalse();
    expect($clonedTemplate->id)->not->toBe($originalTemplate->id);
});

test('può creare versioni di un documento', function () {
    $document = Document::create([
        'title' => 'Documento con Versioni',
        'content' => 'Contenuto iniziale',
        'status' => 'draft',
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    $version1 = $document->createVersion('Versione 1');
    expect($version1->version_name)->toBe('Versione 1');
    expect($version1->content)->toBe('Contenuto iniziale');

    $document->update(['content' => 'Contenuto modificato']);
    $version2 = $document->createVersion('Versione 2');
    expect($version2->version_name)->toBe('Versione 2');
    expect($version2->content)->toBe('Contenuto modificato');
});

test('può clonare un documento con versioning', function () {
    $originalDocument = Document::create([
        'title' => 'Documento Originale',
        'content' => 'Contenuto originale',
        'status' => 'draft',
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    $originalDocument->createVersion('Versione iniziale');

    $cloningService = new DocumentCloningService();
    $clonedDocument = $cloningService->cloneWithVersioning($originalDocument, 'Documento Clonato');

    expect($clonedDocument->title)->toBe('Documento Clonato');
    expect($clonedDocument->parent_id)->toBe($originalDocument->id);
    expect($clonedDocument->versions)->toHaveCount(1);
    expect($clonedDocument->versions->first()->version_name)->toBe('Initial version');
});

test('può clonare multipli documenti', function () {
    $documents = [];
    for ($i = 1; $i <= 3; $i++) {
        $documents[] = Document::create([
            'title' => "Documento {$i}",
            'content' => "Contenuto del documento {$i}",
            'status' => 'draft',
            'author_id' => $this->user->id,
            'version' => 1
        ]);
    }

    $cloningService = new DocumentCloningService();
    $documentIds = collect($documents)->pluck('id')->toArray();
    $clonedDocuments = $cloningService->bulkClone($documentIds, ['status' => 'published']);

    expect($clonedDocuments)->toHaveCount(3);
    foreach ($clonedDocuments as $clonedDocument) {
        expect($clonedDocument->status)->toBe('published');
        expect($clonedDocument->title)->toStartWith('Documento');
    }
});

test('può ottenere statistiche di clonazione', function () {
    $originalDocument = Document::create([
        'title' => 'Documento Originale',
        'content' => 'Contenuto originale',
        'status' => 'draft',
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    $originalDocument->createVersion('Versione 1');
    $originalDocument->createVersion('Versione 2');

    $clonedDocument = $originalDocument->clone('Documento Clonato');
    $clonedDocument->save();

    $cloningService = new DocumentCloningService();
    $stats = $cloningService->getCloningStats($originalDocument);

    expect($stats['original_document'])->toBe('Documento Originale');
    expect($stats['total_children'])->toBe(1);
    expect($stats['total_versions'])->toBe(2);
    expect($stats['total_metadata'])->toBe(0);
});

test('può gestire metadati e impostazioni', function () {
    $document = Document::create([
        'title' => 'Documento con Metadati',
        'content' => 'Contenuto',
        'status' => 'draft',
        'metadata' => ['type' => 'test', 'author' => 'Test'],
        'settings' => ['format' => 'markdown', 'auto_save' => true],
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    expect($document->getMetadataValue('type'))->toBe('test');
    expect($document->getMetadataValue('nonexistent', 'default'))->toBe('default');
    expect($document->getSettingValue('format'))->toBe('markdown');
    expect($document->getSettingValue('nonexistent', false))->toBeFalse();

    $document->setMetadataValue('new_key', 'new_value');
    $document->setSettingValue('new_setting', 'new_value');

    $document->refresh();
    expect($document->getMetadataValue('new_key'))->toBe('new_value');
    expect($document->getSettingValue('new_setting'))->toBe('new_value');
});

test('può pubblicare e archiviare documenti', function () {
    $document = Document::create([
        'title' => 'Documento Test',
        'content' => 'Contenuto',
        'status' => 'draft',
        'author_id' => $this->user->id,
        'version' => 1
    ]);

    expect($document->isDraft())->toBeTrue();
    expect($document->isPublished())->toBeFalse();

    $document->publish();
    expect($document->isPublished())->toBeTrue();
    expect($document->isDraft())->toBeFalse();

    $document->archive();
    expect($document->isArchived())->toBeTrue();
    expect($document->isPublished())->toBeFalse();
});
