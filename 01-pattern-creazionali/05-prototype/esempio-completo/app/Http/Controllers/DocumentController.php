<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentCloningService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    private DocumentCloningService $cloningService;

    public function __construct(DocumentCloningService $cloningService)
    {
        $this->cloningService = $cloningService;
    }

    public function index(): JsonResponse
    {
        $documents = Document::with(['template', 'author', 'versions', 'metadata'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $document = Document::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'template_id' => $request->input('template_id'),
                'status' => $request->input('status', 'draft'),
                'metadata' => $request->input('metadata', []),
                'settings' => $request->input('settings', []),
                'tags' => $request->input('tags', []),
                'author_id' => auth()->id(),
                'version' => 1,
            ]);

            // Crea una versione iniziale
            $document->createVersion('Initial version');

            return response()->json([
                'success' => true,
                'message' => 'Documento creato con successo',
                'data' => $document->load(['template', 'author', 'versions', 'metadata'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione del documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $document->load(['template', 'author', 'versions', 'metadata', 'parent', 'children'])
        ]);
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        try {
            $document->update($request->only([
                'title', 'content', 'status', 'metadata', 'settings', 'tags'
            ]));

            // Crea una nuova versione se il contenuto è cambiato
            if ($request->has('content') && $request->input('content') !== $document->getOriginal('content')) {
                $document->createVersion('Content updated');
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento aggiornato con successo',
                'data' => $document->load(['template', 'author', 'versions', 'metadata'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento del documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Document $document): JsonResponse
    {
        try {
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminato con successo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'eliminazione del documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clone(Document $document, Request $request): JsonResponse
    {
        try {
            $customData = $request->input('custom_data', []);
            $clonedDocument = $this->cloningService->deepClone($document, $customData);

            return response()->json([
                'success' => true,
                'message' => 'Documento clonato con successo',
                'data' => $clonedDocument
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la clonazione del documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cloneWithVersioning(Document $document, Request $request): JsonResponse
    {
        try {
            $newTitle = $request->input('title', 'Copia di ' . $document->title);
            $versionName = $request->input('version_name');
            
            $clonedDocument = $this->cloningService->cloneWithVersioning($document, $newTitle, $versionName);

            return response()->json([
                'success' => true,
                'message' => 'Documento clonato con versioning con successo',
                'data' => $clonedDocument
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la clonazione con versioning',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkClone(Request $request): JsonResponse
    {
        try {
            $documentIds = $request->input('document_ids', []);
            $customData = $request->input('custom_data', []);
            
            if (empty($documentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nessun documento selezionato per la clonazione'
                ], 400);
            }

            $clonedDocuments = $this->cloningService->bulkClone($documentIds, $customData);

            return response()->json([
                'success' => true,
                'message' => count($clonedDocuments) . ' documenti clonati con successo',
                'data' => $clonedDocuments
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la clonazione multipla',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCloningStats(Document $document): JsonResponse
    {
        try {
            $stats = $this->cloningService->getCloningStats($document);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante il recupero delle statistiche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createFromTemplate(DocumentTemplate $template, Request $request): JsonResponse
    {
        try {
            $title = $request->input('title');
            $customData = $request->input('custom_data', []);
            
            if (!$title) {
                return response()->json([
                    'success' => false,
                    'message' => 'Il titolo è obbligatorio'
                ], 400);
            }

            $document = $this->cloningService->cloneFromTemplate($template, $title, $customData);

            return response()->json([
                'success' => true,
                'message' => 'Documento creato dal template con successo',
                'data' => $document
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione dal template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function publish(Document $document): JsonResponse
    {
        try {
            $document->publish();

            return response()->json([
                'success' => true,
                'message' => 'Documento pubblicato con successo',
                'data' => $document
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante la pubblicazione',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function archive(Document $document): JsonResponse
    {
        try {
            $document->archive();

            return response()->json([
                'success' => true,
                'message' => 'Documento archiviato con successo',
                'data' => $document
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'archiviazione',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
