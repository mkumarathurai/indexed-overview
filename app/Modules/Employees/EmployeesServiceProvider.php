<?php

namespace App\Modules\Employees;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\Employees\Http\Livewire\EmployeesIndex;
use App\Modules\Employees\Http\Livewire\EmployeeDetails;
use App\Modules\Employees\Http\Livewire\JiraUsersIndex;
use App\Modules\Employees\Console\Commands\SyncJiraUsers;
use App\Modules\Employees\Console\Commands\ListJiraGroups;
use App\Modules\Employees\Services\JiraProjectService;
use App\Modules\Employees\Console\Commands\SyncEmployees;

class EmployeesServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register services
        $this->app->singleton(\App\Modules\Employees\Services\JiraApiService::class, function ($app) {
            return new \App\Modules\Employees\Services\JiraApiService();
        });
        $this->app->singleton(JiraProjectService::class, function ($app) {
            return new JiraProjectService();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'employees');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Register Livewire Components
        Livewire::component('employees-index', EmployeesIndex::class);
        Livewire::component('employee-details', EmployeeDetails::class);
        Livewire::component('jira-users-index', JiraUsersIndex::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncJiraUsers::class,
                ListJiraGroups::class,
                \App\Modules\Employees\Console\Commands\UpdateEmployeeInfo::class,
                \App\Modules\Employees\Console\Commands\TestWorklogCommand::class,
                SyncEmployees::class
            ]);
        }
    }
}