<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/documents');
});

Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/documents/create', [DocumentController::class, 'createDocument'])->name('documents.create');
Route::post('/documents/render', [DocumentController::class, 'renderDocument'])->name('documents.render');
Route::post('/documents/info', [DocumentController::class, 'getDocumentInfo'])->name('documents.info');
Route::get('/documents/template-stats', [DocumentController::class, 'getTemplateStats'])->name('documents.template-stats');
Route::post('/documents/clear-cache', [DocumentController::class, 'clearTemplateCache'])->name('documents.clear-cache');
Route::get('/documents/all', [DocumentController::class, 'getAllDocuments'])->name('documents.all');
