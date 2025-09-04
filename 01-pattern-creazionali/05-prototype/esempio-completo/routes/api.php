<?php

use App\Http\Controllers\DocumentController;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route per gestione documenti
Route::prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::post('/', [DocumentController::class, 'store']);
    Route::get('/{document}', [DocumentController::class, 'show']);
    Route::put('/{document}', [DocumentController::class, 'update']);
    Route::delete('/{document}', [DocumentController::class, 'destroy']);
    
    // Route per clonazione
    Route::post('/{document}/clone', [DocumentController::class, 'clone']);
    Route::post('/{document}/clone-with-versioning', [DocumentController::class, 'cloneWithVersioning']);
    Route::post('/bulk-clone', [DocumentController::class, 'bulkClone']);
    Route::get('/{document}/cloning-stats', [DocumentController::class, 'getCloningStats']);
    
    // Route per gestione stato
    Route::post('/{document}/publish', [DocumentController::class, 'publish']);
    Route::post('/{document}/archive', [DocumentController::class, 'archive']);
});

// Route per template
Route::prefix('templates')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'data' => DocumentTemplate::with('creator')->get()
        ]);
    });
    
    Route::post('/{template}/create-document', [DocumentController::class, 'createFromTemplate']);
});
