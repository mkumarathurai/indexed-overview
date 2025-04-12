<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Budgets\Http\Controllers\BudgetsController;

Route::middleware(['auth'])->group(function () {
    Route::get('/budgets', [BudgetsController::class, 'index'])->name('budgets.index');
    Route::post('/budgets/save', [BudgetsController::class, 'save'])->name('budgets.save');
    Route::delete('/budgets/delete', [BudgetsController::class, 'delete'])->name('budgets.delete');
});
