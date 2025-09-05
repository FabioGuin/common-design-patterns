<?php

namespace App\Http\Controllers;

use App\Models\BatchJob;
use App\Services\Batch\BatchProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class AIBatchController extends Controller
{
    public function __construct(
        private BatchProcessingService $batchService
    ) {}

    /**
     * Mostra la pagina principale del batch processing
     */
    public function index(): View
    {
        $batches = BatchJob::with('requests')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $statistics = $this->batchService->getBatchStatistics();

        return view('ai-batch-processing.index', compact('batches', 'statistics'));
    }

    /**
     * Crea un nuovo batch
     */
    public function createBatch(Request $request): JsonResponse
    {
        $request->validate([
            'requests' => 'required|array|min:1|max:1000',
            'requests.*.input' => 'required|string|max:10000',
            'requests.*.expected_output' => 'nullable|string|max:10000',
            'requests.*.priority' => 'nullable|in:low,normal,high,urgent',
            'provider' => 'required|in:openai,claude,gemini',
            'model' => 'required|string|max:100',
            'batch_size' => 'nullable|integer|min:1|max:1000',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $batchJob = $this->batchService->createBatch(
                requests: $request->input('requests'),
                provider: $request->input('provider'),
                model: $request->input('model'),
                options: [
                    'batch_size' => $request->input('batch_size', 100),
                    'priority' => $request->input('priority', 'normal'),
                    'name' => $request->input('name'),
                    'metadata' => $request->input('metadata', []),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch creato con successo',
                'data' => [
                    'batch_id' => $batchJob->id,
                    'status' => $batchJob->status,
                    'total_requests' => $batchJob->total_requests,
                    'provider' => $batchJob->provider,
                    'model' => $batchJob->model,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione del batch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Processa un batch
     */
    public function processBatch(int $batchId): JsonResponse
    {
        try {
            $batchJob = BatchJob::findOrFail($batchId);
            
            $this->batchService->processBatch($batchJob);

            return response()->json([
                'success' => true,
                'message' => 'Batch processato con successo',
                'data' => [
                    'batch_id' => $batchJob->id,
                    'status' => $batchJob->fresh()->status,
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel processing del batch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene lo stato di un batch
     */
    public function getBatchStatus(int $batchId): JsonResponse
    {
        try {
            $batchJob = BatchJob::with('requests')->findOrFail($batchId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $batchJob->id,
                    'name' => $batchJob->name,
                    'status' => $batchJob->status,
                    'progress_percentage' => $batchJob->getProgressPercentage(),
                    'processed_requests' => $batchJob->processed_requests,
                    'total_requests' => $batchJob->total_requests,
                    'failed_requests' => $batchJob->failed_requests,
                    'success_rate' => $batchJob->getSuccessRate(),
                    'provider' => $batchJob->provider,
                    'model' => $batchJob->model,
                    'priority' => $batchJob->priority,
                    'scheduled_at' => $batchJob->scheduled_at,
                    'completed_at' => $batchJob->completed_at,
                    'processing_time_seconds' => $batchJob->processing_time_seconds,
                    'estimated_time_remaining' => $batchJob->getEstimatedTimeRemaining(),
                    'throughput' => $batchJob->getThroughput(),
                    'error_message' => $batchJob->error_message,
                    'requests' => $batchJob->requests->map(function ($request) {
                        return [
                            'id' => $request->id,
                            'input' => $request->input,
                            'status' => $request->status,
                            'actual_output' => $request->actual_output,
                            'error_message' => $request->error_message,
                            'processing_time_ms' => $request->processing_time_ms,
                        ];
                    }),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dello stato: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene la lista dei batch
     */
    public function getBatches(Request $request): JsonResponse
    {
        try {
            $query = BatchJob::with('requests');

            // Filtri
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('provider')) {
                $query->where('provider', $request->input('provider'));
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->input('priority'));
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->input('date_from'));
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->input('date_to'));
            }

            // Ordinamento
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginazione
            $perPage = $request->input('per_page', 20);
            $batches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $batches->items(),
                'pagination' => [
                    'current_page' => $batches->currentPage(),
                    'last_page' => $batches->lastPage(),
                    'per_page' => $batches->perPage(),
                    'total' => $batches->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei batch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancella un batch
     */
    public function cancelBatch(int $batchId): JsonResponse
    {
        try {
            $batchJob = BatchJob::findOrFail($batchId);
            
            $this->batchService->cancelBatch($batchJob);

            return response()->json([
                'success' => true,
                'message' => 'Batch cancellato con successo',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella cancellazione: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Riprova un batch fallito
     */
    public function retryBatch(int $batchId): JsonResponse
    {
        try {
            $batchJob = BatchJob::findOrFail($batchId);
            
            $this->batchService->retryBatch($batchJob);

            return response()->json([
                'success' => true,
                'message' => 'Batch riavviato con successo',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel riavvio: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche dei batch
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['provider', 'status', 'date_from', 'date_to']);
            $statistics = $this->batchService->getBatchStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero delle statistiche: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea un batch di esempio per test
     */
    public function createSampleBatch(): JsonResponse
    {
        try {
            $sampleRequests = [
                [
                    'input' => 'Analizza il sentiment di questo testo: "Questo prodotto è fantastico!"',
                    'expected_output' => 'Sentiment: Positivo',
                    'priority' => 'normal',
                ],
                [
                    'input' => 'Traduci in inglese: "Ciao, come stai?"',
                    'expected_output' => 'Hello, how are you?',
                    'priority' => 'normal',
                ],
                [
                    'input' => 'Riassumi questo testo: "Laravel è un framework PHP moderno..."',
                    'expected_output' => 'Riassunto del testo',
                    'priority' => 'normal',
                ],
            ];

            $batchJob = $this->batchService->createBatch(
                requests: $sampleRequests,
                provider: 'openai',
                model: 'gpt-3.5-turbo',
                options: [
                    'name' => 'Batch di esempio - ' . now()->format('Y-m-d H:i:s'),
                    'batch_size' => 3,
                    'priority' => 'normal',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch di esempio creato con successo',
                'data' => [
                    'batch_id' => $batchJob->id,
                    'status' => $batchJob->status,
                    'total_requests' => $batchJob->total_requests,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione del batch di esempio: ' . $e->getMessage(),
            ], 500);
        }
    }
}
