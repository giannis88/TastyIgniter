<?php

namespace System;

use System\Classes\BaseModuleServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Support\Facades\Cache;

class ServiceProvider extends BaseModuleServiceProvider
{
    public $root = __DIR__;

    protected $namespace = 'System\\';

    protected $commands = [
        \Illuminate\Foundation\Console\ServeCommand::class,
        \Illuminate\Cache\Console\ClearCommand::class,
        \Illuminate\Cache\Console\ForgetCommand::class,
        \Illuminate\Cache\Console\CacheTableCommand::class,
        \Illuminate\Foundation\Console\ClearCompiledCommand::class,
        \Illuminate\Foundation\Console\ConfigClearCommand::class,
        \Illuminate\Foundation\Console\ConfigCacheCommand::class,
        \Illuminate\Foundation\Console\DownCommand::class,
        \Illuminate\Foundation\Console\EnvironmentCommand::class,
        \Illuminate\Foundation\Console\EventCacheCommand::class,
        \Illuminate\Foundation\Console\EventClearCommand::class,
        \Illuminate\Foundation\Console\EventListCommand::class,
        \Illuminate\Foundation\Console\KeyGenerateCommand::class,
        \Illuminate\Foundation\Console\OptimizeClearCommand::class,
        \Illuminate\Foundation\Console\OptimizeCommand::class,
        \Illuminate\Foundation\Console\PackageDiscoverCommand::class,
        \Illuminate\Foundation\Console\RouteCacheCommand::class,
        \Illuminate\Foundation\Console\RouteClearCommand::class,
        \Illuminate\Foundation\Console\RouteListCommand::class,
        \Illuminate\Foundation\Console\UpCommand::class,
        \Illuminate\Foundation\Console\ViewClearCommand::class,
        \Illuminate\Foundation\Console\ViewCacheCommand::class,
    ];

    /**
     * Register the service provider.
     *
     * @param  string|null $directory
     * @return void
     */
    public function register($directory = null)
    {
        try {
            $this->app->register(CacheServiceProvider::class);
            Cache::extend(
                'fallback', function () {
                    return Cache::repository(Cache::store('file')->getStore());
                }
            );
        } catch (\Exception $e) {
            // Default to file cache if Redis fails
            config(['cache.default' => 'file']);
        }

        parent::register('system');
    }

    /**
     * Bootstrap the module events.
     *
     * @param  string|null $directory
     * @return void
     */
    public function boot($directory = null)
    {
        parent::boot('system');
    }
}
