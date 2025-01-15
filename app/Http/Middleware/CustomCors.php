<?php

declare(strict_types=1);

/**
 * TastyIgniter CORS Middleware
 *
 * @category    Middleware
 * @package     TastyIgniter\Http\Middleware
 * @author      TastyIgniter Dev Team
 * @copyright   2024 TastyIgniter
 * @license     MIT
 * @link        https://tastyigniter.com
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Handles Cross-Origin Resource Sharing (CORS) requests
 *
 * @category    Middleware
 * @package     TastyIgniter\Http\Middleware
 * @author      TastyIgniter Dev Team
 * @license     MIT
 * @link        https://tastyigniter.com
 */
class CustomCors
{
    /**
     * CORS configuration
     *
     * @var array
     */
    protected array $config;

    /**
     * Allowed origin patterns
     *
     * @var array
     */
    protected array $allowedPatterns;

    /**
     * Initialize the CORS middleware
     *
     * @param array|null $config CORS configuration array
     */
    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->getDefaultConfig();

        $this->allowedPatterns = [
            '#^https?://.*\.tastyigniter\.local$#',
            '#^https?://.*\.localhost$#'
        ];
    }

    /**
     * Get the default CORS configuration
     *
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        $origins = env('CORS_ALLOWED_ORIGINS');
        $methods = env('CORS_ALLOWED_METHODS');
        $headers = env('CORS_ALLOWED_HEADERS');

        return [
            'allowed_origins' => $origins ? array_filter(explode(',', $origins)) : [],
            'allowed_methods' => $methods ? array_filter(explode(',', $methods)) : ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => $headers ? array_filter(explode(',', $headers)) : ['X-Requested-With', 'Content-Type', 'Authorization'],
            'max_age' => (int)env('CORS_MAX_AGE', 3600),
            'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', true), FILTER_VALIDATE_BOOLEAN),
            'debug' => filter_var(env('CORS_DEBUG', env('APP_DEBUG', false)), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Check if the origin is allowed
     *
     * @param string $origin Origin to check
     * @return bool
     */
    protected function isOriginAllowed($origin): bool
    {
        if (in_array($origin, $this->config['allowed_origins'])) {
            return true;
        }

        foreach ($this->allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle an incoming request
     *
     * @param Request $request The request instance
     * @param Closure $next The next middleware
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $origin = $request->header('Origin');

        if ($origin && !$this->isOriginAllowed($origin)) {
            return response()->json(
                [
                    'error' => 'Origin not allowed',
                    'status' => 403
                ],
                403
            );
        }

        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        $response = $next($request);

        if (!method_exists($response, 'header')) {
            return $response;
        }

        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', '86400')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Handle a preflight request
     *
     * @param Request $request The request instance
     * @return Response|JsonResponse
     */
    protected function handlePreflightRequest(Request $request): Response|JsonResponse
    {
        $origin = $request->header('Origin');

        if (!$this->isOriginAllowed($origin)) {
            if ($this->config['debug']) {
                Log::debug("CORS preflight rejected for origin: {$origin}");
            }
            return response(null, 403);
        }

        return response(null, 204)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']))
            ->header('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']))
            ->header('Access-Control-Max-Age', $this->config['max_age']);
    }
}
