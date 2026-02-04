<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions so 500s are visible in storage/logs/laravel.log (grep "500" or "ERROR")
            Log::error('500 ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => request() ? request()->fullUrl() : null,
                'method' => request() ? request()->method() : null,
            ]);
        });
    }

    /**
     * Return JSON for API routes so Play Store app never gets HTML error page.
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        if ($request->is('api/*')) {
            return true;
        }
        return parent::shouldReturnJson($request, $e);
    }
}
