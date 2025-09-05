<?php

use Illuminate\Support\Facades\Route;

Route::get('/bff', function () {
    return view('bff.example');
})->name('bff.index');
