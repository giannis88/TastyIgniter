<?php

namespace System;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->registerProviders();
    }

    protected function registerProviders()
    {
        $this->app->register(\Igniter\Flame\Filesystem\FilesystemServiceProvider::class);
    }

    public function provides()
    {
        return ['system'];
    }
}
