<?php

use App\Modules\Projects\Controllers\ProjectsController;
use App\Modules\Projects\Controllers\ProjectPdfController;

Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/{projectKey}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::post('/projects/refresh', [ProjectsController::class, 'refresh'])->name('projects.refresh');
    Route::get('/projects/{projectKey}/pdf', [ProjectPdfController::class, 'download'])->name('projects.pdf');
});