<?php

use App\Http\Controllers\ExpressionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/expressions');
});

Route::prefix('expressions')->group(function () {
    Route::get('/', [ExpressionController::class, 'index'])->name('expressions.index');
    Route::post('/evaluate-math', [ExpressionController::class, 'evaluateMath'])->name('expressions.evaluate-math');
    Route::post('/evaluate-query', [ExpressionController::class, 'evaluateQuery'])->name('expressions.evaluate-query');
    Route::post('/evaluate-config', [ExpressionController::class, 'evaluateConfig'])->name('expressions.evaluate-config');
    Route::post('/validate', [ExpressionController::class, 'validateExpression'])->name('expressions.validate');
});
