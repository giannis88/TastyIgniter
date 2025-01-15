<?php

define('LARAVEL_START', microtime(true));

// Error handling setup
if (!defined('E_DEPRECATED_MASK')) {
    define('E_DEPRECATED_MASK', E_DEPRECATED | E_USER_DEPRECATED);
}
error_reporting(E_ALL & ~E_DEPRECATED_MASK & ~E_NOTICE);

// Performance settings
ini_set('memory_limit', '1G');
gc_enable();

// Load composer autoloader
$autoloader = require __DIR__.'/../vendor/autoload.php';

// Register application class loader
$autoloader->addPsr4('App\\', __DIR__.'/../app/');

return $autoloader;
