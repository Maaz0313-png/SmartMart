<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : redirect()->guest(route('login'));
        }

        if (!$request->user()->hasAnyRole($roles)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden: Insufficient role permissions'], 403)
                : abort(403, 'Forbidden: Insufficient role permissions');
        }

        return $next($request);
    }
}