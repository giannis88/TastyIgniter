<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Logging\LoggerFactory;
use Monolog\Logger as Monolog;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('log', function ($app) {
            $monolog = new Monolog($app->environment());
            return LoggerFactory::createLogger($monolog, $app['events']);
        });
    }

    public function provides()
    {
        return ['log'];
    }
}
