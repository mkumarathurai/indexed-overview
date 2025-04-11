<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect root to login if not authenticated
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth Routes - Login only
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard::index');
    })->name('dashboard');
    
    Route::get('/projects', 'App\Modules\Projects\Controllers\ProjectsController@index')->name('projects.index');
    
    Route::get('/tempo-hours', \App\Modules\TempoHours\Http\Livewire\TempoHoursIndex::class)->name('tempo-hours.index');
    
    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/profile', function () {
            return view('settings.profile');
        })->name('profile');
    });
});

require __DIR__.'/auth.php';
