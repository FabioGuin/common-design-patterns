<?php

use App\Http\Controllers\DocumentController;
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

Route::get('/', function () {
    return redirect('/documents');
});

// Rotte per il Command Pattern
Route::prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/execute-command', [DocumentController::class, 'executeCommand'])->name('documents.execute-command');
    Route::post('/undo', [DocumentController::class, 'undo'])->name('documents.undo');
    Route::post('/redo', [DocumentController::class, 'redo'])->name('documents.redo');
    Route::post('/execute-macro', [DocumentController::class, 'executeMacro'])->name('documents.execute-macro');
    Route::get('/history', [DocumentController::class, 'getHistory'])->name('documents.history');
});
