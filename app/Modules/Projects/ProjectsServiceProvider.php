<?php

namespace App\Modules\Projects;

use Illuminate\Support\ServiceProvider;

class ProjectsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'projects');
        
        // Register Livewire Components
        \Livewire\Livewire::component('projects-table', \App\Modules\Projects\Http\Livewire\ProjectsTable::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Register services
        $this->app->singleton('ProjectsApiService', function ($app) {
            return new \App\Modules\Projects\Services\ProjectsApiService();
        });

        // Register console commands
        $this->commands([
            \App\Modules\Projects\Console\Commands\SyncProjects::class,
            \App\Modules\Projects\Console\Commands\FetchArchivedProjects::class,
        ]);
    }
}