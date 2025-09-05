<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedDatabaseController;

/*
|--------------------------------------------------------------------------
| Web Routes per Shared Database Anti-pattern
|--------------------------------------------------------------------------
|
| Queste route dimostrano i problemi del Shared Database Anti-pattern
| dove multiple servizi condividono lo stesso database.
|
*/

// Route principale per l'esempio
Route::get('/shared-database/example', [SharedDatabaseController::class, 'example'])
    ->name('shared-database.example');

// Route per le operazioni CRUD
Route::post('/shared-database/create-user', [SharedDatabaseController::class, 'createUser'])
    ->name('shared-database.create-user');

Route::post('/shared-database/create-product', [SharedDatabaseController::class, 'createProduct'])
    ->name('shared-database.create-product');

Route::post('/shared-database/create-order', [SharedDatabaseController::class, 'createOrder'])
    ->name('shared-database.create-order');

Route::post('/shared-database/create-payment', [SharedDatabaseController::class, 'createPayment'])
    ->name('shared-database.create-payment');

Route::post('/shared-database/process-payment', [SharedDatabaseController::class, 'processPayment'])
    ->name('shared-database.process-payment');

// Route per i test dei problemi del pattern
Route::post('/shared-database/simulate-deadlock', [SharedDatabaseController::class, 'simulateDeadlock'])
    ->name('shared-database.simulate-deadlock');

Route::post('/shared-database/simulate-complex-transaction', [SharedDatabaseController::class, 'simulateComplexTransaction'])
    ->name('shared-database.simulate-complex-transaction');

Route::post('/shared-database/test-scalability', [SharedDatabaseController::class, 'testScalability'])
    ->name('shared-database.test-scalability');

// Route per le statistiche e il monitoring
Route::get('/shared-database/stats', [SharedDatabaseController::class, 'getStats'])
    ->name('shared-database.stats');

Route::get('/shared-database/conflict-history', [SharedDatabaseController::class, 'getConflictHistory'])
    ->name('shared-database.conflict-history');

Route::get('/shared-database/lock-history', [SharedDatabaseController::class, 'getLockHistory'])
    ->name('shared-database.lock-history');
