<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Holidays\Http\Controllers\HolidayController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/', [HolidayController::class, 'index'])->name('holidays.index');
}); 