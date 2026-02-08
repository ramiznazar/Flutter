<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalSecret
{
    /**
     * Verify that the request carries the internal API secret (gateway-only).
     * Expects header: X-Internal-Secret: <INTERNAL_API_SECRET> or Authorization: Bearer <INTERNAL_API_SECRET>
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.internal_api_secret') ?: env('INTERNAL_API_SECRET');
        if (empty($secret)) {
            return response()->json(['success' => false, 'message' => 'Internal API not configured'], 503);
        }

        $provided = $request->header('X-Internal-Secret')
            ?? preg_replace('/^Bearer\s+/i', '', $request->header('Authorization', ''));
        if (! hash_equals((string) $secret, (string) $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
