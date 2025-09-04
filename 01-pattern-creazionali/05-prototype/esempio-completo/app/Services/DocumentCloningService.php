<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\DB;

class DocumentCloningService
{
    public function deepClone(Document $document, array $customData = []): Document
    {
        return DB::transaction(function () use ($document, $customData) {
            $clone = $document->cloneWithCustomData($customData);
            $clone->save();

            // Clona le versioni
            foreach ($document->versions as $version) {
                $versionClone = $version->replicate();
                $versionClone->document_id = $clone->id;
                $versionClone->save();
            }

            // Clona i metadati
            foreach ($document->metadata as $metadata) {
                $metadataClone = $metadata->replicate();
                $metadataClone->document_id = $clone->id;
                $metadataClone->save();
            }

            return $clone->load(['versions', 'metadata']);
        });
    }

    public function cloneFromTemplate(DocumentTemplate $template, string $title, array $customData = []): Document
    {
        return DB::transaction(function () use ($template, $title, $customData) {
            $document = $template->createDocument($title, $customData);
            
            // Crea una versione iniziale
            $document->createVersion('Initial version');
            
            return $document->load(['template', 'versions', 'metadata']);
        });
    }

    public function cloneWithVersioning(Document $document, string $newTitle, string $versionName = null): Document
    {
        return DB::transaction(function () use ($document, $newTitle, $versionName) {
            // Crea una versione del documento originale
            $document->createVersion($versionName ?? 'Pre-clone version');
            
            // Clona il documento
            $clone = $document->cloneWithCustomData([
                'title' => $newTitle,
                'version' => 1,
                'parent_id' => $document->id
            ]);
            $clone->save();
            
            // Crea una versione iniziale per il clone
            $clone->createVersion('Initial version');
            
            return $clone->load(['parent', 'versions', 'metadata']);
        });
    }

    public function bulkClone(array $documentIds, array $customData = []): array
    {
        $clonedDocuments = [];
        
        foreach ($documentIds as $documentId) {
            $document = Document::findOrFail($documentId);
            $clonedDocuments[] = $this->deepClone($document, $customData);
        }
        
        return $clonedDocuments;
    }

    public function cloneTemplateWithDocuments(DocumentTemplate $template, string $newTemplateName, array $customData = []): DocumentTemplate
    {
        return DB::transaction(function () use ($template, $newTemplateName, $customData) {
            // Clona il template
            $templateClone = $template->cloneTemplate($newTemplateName);
            $templateClone->save();
            
            // Clona tutti i documenti del template originale
            foreach ($template->documents as $document) {
                $documentClone = $document->cloneWithCustomData(array_merge($customData, [
                    'template_id' => $templateClone->id,
                    'title' => $document->title . ' (Cloned)'
                ]));
                $documentClone->save();
            }
            
            return $templateClone->load('documents');
        });
    }

    public function getCloningStats(Document $document): array
    {
        return [
            'original_document' => $document->title,
            'total_children' => $document->children()->count(),
            'total_versions' => $document->versions()->count(),
            'total_metadata' => $document->metadata()->count(),
            'last_cloned' => $document->children()->latest()->first()?->created_at,
        ];
    }
}
