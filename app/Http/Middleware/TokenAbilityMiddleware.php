<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TokenAbilityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = $request->user()->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Check if token has required abilities
        foreach ($abilities as $ability) {
            if (!$token->can($ability)) {
                return response()->json([
                    'message' => "Forbidden: Missing '{$ability}' ability"
                ], 403);
            }
        }

        return $next($request);
    }
}