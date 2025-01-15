<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CorsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            'cors', function ($app) {
                return new \App\Http\Middleware\CustomCors();
            }
        );
    }

    public function boot()
    {
        // Load CORS config
        $this->publishes(
            [
            __DIR__.'/../config/cors.php' => config_path('cors.php'),
            ]
        );
    }
}
