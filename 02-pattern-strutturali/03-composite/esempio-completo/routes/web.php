<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/menu');
});

Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::post('/menu/add', [MenuController::class, 'addItem'])->name('menu.add');
Route::post('/menu/remove', [MenuController::class, 'removeItem'])->name('menu.remove');
Route::post('/menu/search', [MenuController::class, 'searchItem'])->name('menu.search');
Route::get('/menu/stats', [MenuController::class, 'getStats'])->name('menu.stats');
