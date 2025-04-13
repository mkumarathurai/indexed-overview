<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Employees\Http\Controllers\EmployeesController;
use App\Modules\Employees\Http\Controllers\JiraUsersController;
use App\Modules\Employees\Http\Livewire\EmployeesIndex;

// Since we want the employees index to show Jira users, we'll redirect from jira-users to employees
Route::middleware(['auth'])->group(function () {
    // Internal employees routes (only indexed.dk employees)
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeesController::class, 'show'])->name('employees.show');
    
    // Jira users routes (all users from Jira)
    Route::get('/jira-users', [JiraUsersController::class, 'index'])->name('jira-users.index');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/employees', EmployeesIndex::class)->name('employees.index');
});