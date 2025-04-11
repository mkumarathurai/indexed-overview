<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            if (file_exists(base_path('routes/api.php'))) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));
            }

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Register Projects Module Routes
            Route::middleware('web')
                ->group(base_path('app/Modules/Projects/Routes/web.php'));
                
            // Register Budgets Module Routes
            Route::middleware('web')
                ->group(base_path('app/Modules/Budgets/Routes/web.php'));
        });
    }
}
