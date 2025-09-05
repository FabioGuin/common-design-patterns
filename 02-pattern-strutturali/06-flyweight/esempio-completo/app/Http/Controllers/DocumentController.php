<?php

namespace App\Http\Controllers;

use App\Services\DocumentTemplateFactory;
use App\Services\DocumentContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    private DocumentTemplateFactory $templateFactory;
    private array $documents = [];

    public function __construct(DocumentTemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * Mostra la pagina principale dei documenti
     */
    public function index()
    {
        $templateStats = $this->templateFactory->getStats();
        $availableTemplates = $this->getAvailableTemplates();

        return view('documents.index', [
            'templateStats' => $templateStats,
            'availableTemplates' => $availableTemplates,
            'documents' => $this->documents,
        ]);
    }

    /**
     * Crea un nuovo documento
     */
    public function createDocument(Request $request): JsonResponse
    {
        $request->validate([
            'template_name' => 'required|string',
            'template_layout' => 'required|string',
            'template_style' => 'required|string',
            'title' => 'required|string|max:200',
            'author' => 'required|string|max:100',
            'data' => 'required|array',
        ]);

        try {
            // Ottiene il template dalla factory (riutilizza se esiste)
            $template = $this->templateFactory->getTemplate(
                $request->template_name,
                $request->template_layout,
                $request->template_style
            );

            // Crea il contesto del documento
            $documentId = 'DOC_' . uniqid();
            $document = new DocumentContext(
                $template,
                $request->data,
                $documentId,
                $request->title,
                $request->author
            );

            // Salva il documento
            $this->documents[$documentId] = $document;

            return response()->json([
                'success' => true,
                'message' => 'Document created successfully',
                'document_id' => $documentId,
                'document_info' => $document->getInfo(),
                'template_stats' => $this->templateFactory->getStats(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Renderizza un documento
     */
    public function renderDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|string',
        ]);

        try {
            $documentId = $request->document_id;

            if (!isset($this->documents[$documentId])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                ], 404);
            }

            $document = $this->documents[$documentId];
            $renderedContent = $document->render();

            return response()->json([
                'success' => true,
                'document_id' => $documentId,
                'rendered_content' => $renderedContent,
                'document_info' => $document->getInfo(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rendering the document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le informazioni di un documento
     */
    public function getDocumentInfo(Request $request): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|string',
        ]);

        try {
            $documentId = $request->document_id;

            if (!isset($this->documents[$documentId])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                ], 404);
            }

            $document = $this->documents[$documentId];

            return response()->json([
                'success' => true,
                'document_info' => $document->getInfo(),
                'document_array' => $document->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving document information',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche dei template
     */
    public function getTemplateStats(): JsonResponse
    {
        try {
            $stats = $this->templateFactory->getStats();

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving template stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pulisce la cache dei template
     */
    public function clearTemplateCache(): JsonResponse
    {
        try {
            $this->templateFactory->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Template cache cleared successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing template cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene tutti i documenti
     */
    public function getAllDocuments(): JsonResponse
    {
        try {
            $documents = [];
            foreach ($this->documents as $document) {
                $documents[] = $document->toArray();
            }

            return response()->json([
                'success' => true,
                'documents' => $documents,
                'count' => count($documents),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving documents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene i template disponibili
     */
    private function getAvailableTemplates(): array
    {
        return [
            'business' => [
                'name' => 'Business',
                'layouts' => ['single-column', 'two-column'],
                'styles' => ['formal', 'casual']
            ],
            'creative' => [
                'name' => 'Creative',
                'layouts' => ['single-column', 'three-column'],
                'styles' => ['modern']
            ],
            'technical' => [
                'name' => 'Technical',
                'layouts' => ['single-column'],
                'styles' => ['monospace']
            ]
        ];
    }
}
