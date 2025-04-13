<?php

namespace App\Modules\Holidays;

use Illuminate\Support\ServiceProvider;
use App\Modules\Holidays\Console\Commands\UpdateHolidaysCommand;
use Livewire\Livewire;
use App\Modules\Holidays\Http\Livewire\HolidayList;
use App\Modules\Holidays\Http\Livewire\HolidayInfo;
use App\Modules\Holidays\Http\Livewire\HolidayStats;

class HolidaysServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(HolidaysRouteServiceProvider::class);
        
        // Register commands
        $this->commands([
            UpdateHolidaysCommand::class,
        ]);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'holidays');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        // Register Livewire components
        Livewire::component('holidays::holiday-info', HolidayInfo::class);
        Livewire::component('holidays::holiday-list', HolidayList::class);
        Livewire::component('holidays::holiday-stats', HolidayStats::class);
    }
} 