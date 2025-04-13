<?php

namespace App\Modules\Employees;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class EmployeesRouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Modules\Employees\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('app/Modules/Employees/Routes/web.php'));
    }
} 