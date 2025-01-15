<?php

define('LARAVEL_START', microtime(true));

// Basic error handling
if (!defined('E_DEPRECATED_MASK')) {
    define('E_DEPRECATED_MASK', E_DEPRECATED | E_USER_DEPRECATED);
}

error_reporting(E_ALL & ~E_DEPRECATED_MASK & ~E_NOTICE);

// Early deprecation handler for vendor files
$vendorPath = realpath(__DIR__ . '/../vendor');
if ($vendorPath) {
    set_error_handler(static function(int $errno, string $errstr, string $errfile) use ($vendorPath): bool {
        return str_starts_with($errfile, $vendorPath) && ($errno & E_DEPRECATED_MASK) !== 0;
    }, E_DEPRECATED_MASK);
}

// Performance settings
ini_set('memory_limit', '1G');
gc_enable();

// Load the composer autoloader
require $vendorPath . '/autoload.php';
