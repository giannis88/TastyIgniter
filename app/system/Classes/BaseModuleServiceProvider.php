<?php

namespace System\Classes;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;

class BaseModuleServiceProvider extends ServiceProvider
{
    protected $namespace;

    public $root;

    protected $commands = [];

    protected function registerNamespaces($directory)
    {
        if (!$this->namespace || !$this->root) {
            return;
        }

        $this->app['config']->set('app.namespaces.' . $directory, $this->namespace);
    }

    public function register($directory = null)
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->registerNamespaces($directory);
        $this->registerCommands();
    }

    public function boot($directory = null)
    {
        if ($directory) {
            $this->loadViewsFrom($this->root . '/views', $directory);
            $this->loadTranslationsFrom($this->root . '/language', $directory);
            $this->loadMigrationsFrom($this->root . '/database/migrations');
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole() && !empty($this->commands)) {
            $this->commands($this->commands);
        }
    }
}
