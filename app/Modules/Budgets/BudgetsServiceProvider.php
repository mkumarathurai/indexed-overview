<?php

namespace App\Modules\Budgets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Budgets\Http\Livewire\BudgetsIndex;
use Livewire\Livewire;

class BudgetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(BudgetsRouteServiceProvider::class);
    }

    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'budgets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Register Livewire components
        Livewire::component('budgets-index', BudgetsIndex::class);
    }
}
