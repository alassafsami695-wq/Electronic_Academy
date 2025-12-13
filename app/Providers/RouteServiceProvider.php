<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Home path after login.
     */
    public const HOME = '/home';

    /**
     * Controller namespace for Laravel 12.
     */
    protected ?string $namespace = 'App\\Http\\Controllers';

    /**
     * Boot method.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Load API routes
            Route::middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // Load Web routes
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });

       

    }

    /**
     * Rate limiting (optional)
     */
    protected function configureRateLimiting(): void
    {
        //
    }
}   
