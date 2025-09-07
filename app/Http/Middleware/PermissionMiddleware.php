<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : redirect()->guest(route('login'));
        }

        if (!$request->user()->hasAnyPermission($permissions)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden: Insufficient permissions'], 403)
                : abort(403, 'Forbidden: Insufficient permissions');
        }

        return $next($request);
    }
}