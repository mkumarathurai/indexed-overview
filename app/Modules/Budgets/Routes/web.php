<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Budgets\Http\Controllers\BudgetsController;

Route::middleware(['auth'])->group(function () {
    Route::get('/budgets', [BudgetsController::class, 'index'])->name('budgets.index');
});
