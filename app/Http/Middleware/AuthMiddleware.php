<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthMiddleware
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        if (!config('gateway.auth.enabled')) {
            return $next($request);
        }

        if (config('gateway.auth.mock')) {
            // Mock JWT authentication
            $token = $request->bearerToken();
            //Check JWT bearer token to be the same with MOCK_JWT_TOKEN from env
            if (!$token || $token !== env('MOCK_JWT_TOKEN')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid or missing mock JWT token'
                ], 401);
            }

            return $next($request);
        }

        // Real JWT authentication would go here
        // For example, using Laravel Sanctum:
        // if (!auth()->guard('sanctum')->check()) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        return $next($request);
    }
}
