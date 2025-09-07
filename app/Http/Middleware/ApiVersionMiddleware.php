<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $version = 'v1'): Response
    {
        // Set API version in request for use in controllers
        $request->attributes->set('api_version', $version);
        
        // Add version header to response
        $response = $next($request);
        
        if ($response instanceof Response) {
            $response->headers->set('X-API-Version', $version);
        }
        
        return $response;
    }
}