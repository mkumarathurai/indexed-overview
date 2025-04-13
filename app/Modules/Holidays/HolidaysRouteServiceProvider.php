<?php

namespace App\Modules\Holidays;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class HolidaysRouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->routes(function () {
            Route::middleware('web')
                ->prefix('holidays')
                ->group(__DIR__.'/routes/web.php');
        });
    }
} 