<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Log the incoming request
        $this->logRequest($request);

        $response = $next($request);

        // Log the response
        $this->logResponse($request, $response, $startTime);

        return $response;
    }

    /**
     * Log incoming request details
     */
    private function logRequest(Request $request): void
    {
        $data = [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->getKey(),
            'timestamp' => now()->toISOString(),
        ];

        // Don't log sensitive data
        if (!in_array($request->path(), ['api/v1/auth/login', 'api/v1/auth/register'])) {
            $data['payload'] = $request->except(['password', 'password_confirmation', 'token']);
        }

        Log::channel('audit')->info('API Request', $data);
    }

    /**
     * Log response details
     */
    private function logResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

        $data = [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_id' => $request->user()?->getKey(),
            'timestamp' => now()->toISOString(),
        ];

        $logLevel = $response->getStatusCode() >= 400 ? 'warning' : 'info';
        Log::channel('audit')->{$logLevel}('API Response', $data);
    }
}