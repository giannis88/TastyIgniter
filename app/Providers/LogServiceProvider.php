<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Logging\LoggerFactory;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('log', function ($app) {
            return (new LoggerFactory())->createLogger($app['config']['logging']);
        });
    }

    public function provides()
    {
        return ['log'];
    }
}
