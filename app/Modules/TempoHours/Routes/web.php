<?php

use Illuminate\Support\Facades\Route;
use App\Modules\TempoHours\Http\Web\Livewire\Admin\TempoHoursIndex as AdminTempoHoursIndex;
use App\Modules\TempoHours\Http\Web\Controllers\Admin\TempoHoursController as AdminTempoHoursController;
use App\Modules\TempoHours\Http\Livewire\TempoHoursIndex;
use App\Modules\TempoHours\Http\Controllers\TempoHoursController;

// Admin routes
Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'auth'],
    'as' => 'admin.',
], function () {
    Route::get('/tempo-hours', AdminTempoHoursIndex::class)->name('tempo-hours.index');
    Route::get('/tempo-hours/{projectKey}/details', [AdminTempoHoursController::class, 'details'])->name('tempo-hours.details');
    Route::get('/tempo-hours/{projectKey}/pdf', [AdminTempoHoursController::class, 'downloadPdf'])->name('tempo-hours.download-pdf');
});

// Regular user routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/tempo-hours', TempoHoursIndex::class)->name('tempo-hours.index');
    Route::get('/tempo-hours/{projectKey}/details', [TempoHoursController::class, 'details'])->name('tempo-hours.details');
    Route::get('/tempo-hours/{projectKey}/pdf', [TempoHoursController::class, 'downloadPdf'])->name('tempo-hours.download-pdf');
    Route::post('/tempo-hours/refresh', [TempoHoursController::class, 'refresh'])->name('tempo-hours.refresh');
});
