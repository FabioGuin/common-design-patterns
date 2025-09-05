<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SagaChoreographyController;

Route::get('/saga-choreography', [SagaChoreographyController::class, 'index'])->name('saga-choreography.index');
Route::post('/saga-choreography/start', [SagaChoreographyController::class, 'startSaga'])->name('saga-choreography.start');
Route::get('/saga-choreography/events/{sagaId}', [SagaChoreographyController::class, 'getEvents'])->name('saga-choreography.events');
Route::get('/saga-choreography/status/{sagaId}', [SagaChoreographyController::class, 'getStatus'])->name('saga-choreography.status');
