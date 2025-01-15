<?php

require __DIR__.'/../vendor/autoload.php';

$app = new \App\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Error handling
if (!defined('E_DEPRECATED_MASK')) {
    define('E_DEPRECATED_MASK', E_DEPRECATED | E_USER_DEPRECATED);
}
error_reporting(E_ALL & ~E_DEPRECATED_MASK & ~E_NOTICE);

// Performance settings
ini_set('memory_limit', '1G');
gc_enable();

/*
|--------------------------------------------------------------------------
| Register Core Service Providers
|--------------------------------------------------------------------------
*/

//$app->register(\Illuminate\Cache\CacheServiceProvider::class);
//$app->register(\Illuminate\Redis\RedisServiceProvider::class);
//$app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    Igniter\Flame\Foundation\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    Igniter\Flame\Foundation\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Igniter\Flame\Foundation\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
