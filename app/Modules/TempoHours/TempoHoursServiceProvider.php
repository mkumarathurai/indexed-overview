<?php

namespace App\Modules\TempoHours;

use Illuminate\Support\ServiceProvider;
use App\Modules\TempoHours\Console\Commands\SyncTempoHours;
use App\Modules\TempoHours\Services\TempoApiService;
use Livewire\Livewire;
use App\Modules\TempoHours\Http\Livewire\TempoHoursIndex;
use App\Modules\TempoHours\Http\Livewire\TempoHourDetails;

class TempoHoursServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TempoApiService::class, function ($app) {
            return new TempoApiService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTempoHours::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'tempo-hours');

        // Register Livewire components
        Livewire::component('tempo-hours-index', TempoHoursIndex::class);
        Livewire::component('tempo-hour-details', TempoHourDetails::class);
    }
}
