<?php

use App\Http\Controllers\LogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route di base per testare il singleton logger
Route::get('/', function () {
    $logger = \App\Services\Logger\LoggerService::getInstance();
    
    $logger->info('Homepage accessed', [
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'timestamp' => now()
    ]);
    
    return response()->json([
        'message' => 'Singleton Logger Example - Laravel',
        'description' => 'Sistema di logging con Singleton Pattern',
        'endpoints' => [
            'GET /logs' => 'Lista tutti i logs',
            'GET /logs/level/{level}' => 'Logs per livello specifico',
            'POST /logs' => 'Crea nuovo log',
            'GET /logs/stats' => 'Statistiche logs',
            'DELETE /logs' => 'Cancella tutti i logs',
            'GET /logs/test/singleton' => 'Test singleton pattern',
            'GET /logs/test/levels' => 'Test tutti i livelli'
        ],
        'current_logs_count' => count($logger->getLogs())
    ]);
});

// API Routes per la gestione dei logs
Route::prefix('logs')->group(function () {
    // Lista tutti i logs
    Route::get('/', [LogController::class, 'index']);
    
    // Logs per livello specifico
    Route::get('/level/{level}', [LogController::class, 'getByLevel']);
    
    // Crea nuovo log
    Route::post('/', [LogController::class, 'store']);
    
    // Statistiche logs
    Route::get('/stats', [LogController::class, 'stats']);
    
    // Cancella tutti i logs
    Route::delete('/', [LogController::class, 'destroy']);
    
    // Test routes
    Route::get('/test/singleton', [LogController::class, 'testSingleton']);
    Route::get('/test/levels', [LogController::class, 'testAllLevels']);
});

// Route di esempio per dimostrare l'uso del logger
Route::get('/example/user-action', function () {
    $logger = \App\Services\Logger\LoggerService::getInstance();
    
    // Simula un'azione utente
    $userId = rand(1, 1000);
    $action = 'view_profile';
    
    $logger->info('User action performed', [
        'user_id' => $userId,
        'action' => $action,
        'ip' => request()->ip(),
        'timestamp' => now()
    ]);
    
    return response()->json([
        'message' => 'User action logged successfully',
        'user_id' => $userId,
        'action' => $action
    ]);
});

Route::get('/example/error-simulation', function () {
    $logger = \App\Services\Logger\LoggerService::getInstance();
    
    try {
        // Simula un errore
        throw new \Exception('Simulated database connection error');
    } catch (\Exception $e) {
        $logger->error('Database connection failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'ip' => request()->ip()
        ]);
        
        return response()->json([
            'message' => 'Error simulated and logged',
            'error' => $e->getMessage()
        ], 500);
    }
});
