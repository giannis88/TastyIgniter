<?php

namespace Main;

use System\Classes\BaseModuleServiceProvider;

class ServiceProvider extends BaseModuleServiceProvider
{
    public $root = __DIR__;

    protected $namespace = 'Main\\';

    /**
     * Register the service provider.
     *
     * @param  string|null $directory
     * @return void
     */
    public function register($directory = null)
    {
        parent::register('main');
    }

    /**
     * Bootstrap the module events.
     *
     * @param  string|null $directory
     * @return void
     */
    public function boot($directory = null)
    {
        parent::boot('main');
    }
}
