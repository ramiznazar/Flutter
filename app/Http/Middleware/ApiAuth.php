<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization') 
            ?? $request->input('auth_token') 
            ?? $request->bearerToken();

        // Remove 'Bearer ' prefix if present
        if ($token && strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (!$token) {
            return response()->json([
                'status' => 401,
                'message' => 'Authentication token required.'
            ], 401);
        }

        $user = User::where('auth_token', $token)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid or expired authentication token.'
            ], 401);
        }

        // Attach user to request for use in controllers
        $request->merge(['auth_user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}



