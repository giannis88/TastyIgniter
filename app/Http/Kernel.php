<?php

/**
 * TastyIgniter HTTP Kernel
 *
 * @category    Http
 * @package     TastyIgniter\Http
 * @author      TastyIgniter Dev Team
 * @copyright   2024 TastyIgniter
 * @license     MIT
 * @link        https://tastyigniter.com
 */

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * HTTP Kernel Configuration
 *
 * @category    Http
 * @package     TastyIgniter\Http
 */
class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CustomCors::class,
        // ...existing middleware...
    ];

    protected $middlewareGroups = [
        'web' => [
            // ...existing web middleware...
        ],
        'api' => [
            // ...existing api middleware...
        ],
    ];

    protected $routeMiddleware = [
        // ...existing middleware...
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];

    // ...rest of the file...
}
