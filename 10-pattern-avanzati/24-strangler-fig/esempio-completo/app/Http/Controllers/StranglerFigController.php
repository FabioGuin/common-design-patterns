<?php

namespace App\Http\Controllers;

use App\Services\StranglerFigService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StranglerFigController extends Controller
{
    private StranglerFigService $stranglerFig;

    public function __construct(StranglerFigService $stranglerFig)
    {
        $this->stranglerFig = $stranglerFig;
    }

    /**
     * Mostra la dashboard del Strangler Fig
     */
    public function index(): View
    {
        $migrationStatus = $this->stranglerFig->getMigrationStatus();
        $migrationStats = $this->stranglerFig->getMigrationStats();

        return view('strangler-fig.example', compact(
            'migrationStatus', 
            'migrationStats'
        ));
    }

    /**
     * Testa il Strangler Fig
     */
    public function test(Request $request): JsonResponse
    {
        $feature = $request->input('feature', 'users');
        $numRequests = $request->input('requests', 10);

        $testResult = $this->stranglerFig->testFeature($feature, $numRequests);

        return response()->json([
            'success' => true,
            'data' => $testResult,
            'pattern_id' => $this->stranglerFig->getId(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Ottiene lo stato della migrazione
     */
    public function status(): JsonResponse
    {
        $migrationStatus = $this->stranglerFig->getMigrationStatus();
        $migrationStats = $this->stranglerFig->getMigrationStats();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $migrationStatus,
                'stats' => $migrationStats
            ]
        ]);
    }

    /**
     * Avvia la migrazione di una funzionalità
     */
    public function migrateFeature(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string|in:users,products,orders',
            'percentage' => 'integer|min:0|max:100'
        ]);

        $feature = $request->input('feature');
        $percentage = $request->input('percentage', 0);

        $success = $this->stranglerFig->startMigration($feature, $percentage);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Migration started for feature: {$feature}",
                'data' => [
                    'feature' => $feature,
                    'percentage' => $percentage
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to start migration for feature: {$feature}"
        ], 400);
    }

    /**
     * Fa rollback di una funzionalità
     */
    public function rollbackFeature(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string|in:users,products,orders'
        ]);

        $feature = $request->input('feature');
        $success = $this->stranglerFig->rollbackMigration($feature);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Rollback completed for feature: {$feature}",
                'data' => [
                    'feature' => $feature
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to rollback feature: {$feature}"
        ], 400);
    }

    /**
     * Aggiorna la percentuale di migrazione
     */
    public function updateMigrationPercentage(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string|in:users,products,orders',
            'percentage' => 'required|integer|min:0|max:100'
        ]);

        $feature = $request->input('feature');
        $percentage = $request->input('percentage');

        $success = $this->stranglerFig->updateMigrationPercentage($feature, $percentage);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Migration percentage updated for feature: {$feature}",
                'data' => [
                    'feature' => $feature,
                    'percentage' => $percentage
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to update migration percentage for feature: {$feature}"
        ], 400);
    }

    /**
     * Completa la migrazione di una funzionalità
     */
    public function completeMigration(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string|in:users,products,orders'
        ]);

        $feature = $request->input('feature');
        $success = $this->stranglerFig->completeMigration($feature);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => "Migration completed for feature: {$feature}",
                'data' => [
                    'feature' => $feature
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to complete migration for feature: {$feature}"
        ], 400);
    }

    /**
     * Ottiene la lista delle funzionalità
     */
    public function features(): JsonResponse
    {
        $migrationStatus = $this->stranglerFig->getMigrationStatus();
        $features = [];

        foreach ($migrationStatus as $feature => $config) {
            $features[] = [
                'name' => $feature,
                'status' => $config['status'],
                'percentage' => $config['percentage'],
                'startDate' => $config['startDate'],
                'endDate' => $config['endDate']
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $features
        ]);
    }

    /**
     * Testa una richiesta specifica
     */
    public function testRequest(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string|in:users,products,orders',
            'user_id' => 'integer|min:0'
        ]);

        $feature = $request->input('feature');
        $userId = $request->input('user_id', 1);

        $requestData = [
            'user_id' => $userId,
            'request_id' => uniqid(),
            'timestamp' => now()->toISOString()
        ];

        try {
            $result = $this->stranglerFig->routeRequest($feature, $requestData);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche dettagliate
     */
    public function stats(): JsonResponse
    {
        $migrationStatus = $this->stranglerFig->getMigrationStatus();
        $migrationStats = $this->stranglerFig->getMigrationStats();

        $detailedStats = [];
        foreach ($migrationStatus as $feature => $config) {
            $detailedStats[] = [
                'feature' => $feature,
                'status' => $config['status'],
                'percentage' => $config['percentage'],
                'startDate' => $config['startDate'],
                'endDate' => $config['endDate'],
                'duration' => $config['endDate'] ? 
                    $this->calculateDuration($config['startDate'], $config['endDate']) : null
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'features' => $detailedStats,
                'overall_stats' => $migrationStats,
                'pattern_id' => $this->stranglerFig->getId()
            ]
        ]);
    }

    /**
     * Calcola la durata della migrazione
     */
    private function calculateDuration(?string $startDate, ?string $endDate): ?string
    {
        if (!$startDate || !$endDate) {
            return null;
        }

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        return $start->diffForHumans($end, true);
    }
}
