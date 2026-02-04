<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs ThrottleRequests but catches cache/IO errors (e.g. missing cache dir)
 * and allows the request through instead of returning 500, so the app stays live.
 */
class ThrottleRequestsResilient
{
    public function __construct(
        protected ThrottleRequests $throttle
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        try {
            return $this->throttle->handle($request, $next, $limiter);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Create missing cache subdir so next request for same key can succeed (avoids repeated 500s on booster routes)
            if (str_contains($msg, 'No such file or directory') && str_contains($msg, 'cache/data')) {
                if (preg_match('/fopen\(([^)]+)\)/', $msg, $m)) {
                    $path = trim($m[1]);
                    $dir = dirname($path);
                    if (str_contains($dir, 'cache/data') && !is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                }
            }
            Log::warning('ThrottleRequests failed, allowing request through', [
                'message' => $msg,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
            return $next($request);
        }
    }
}
