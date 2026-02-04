<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureCacheDataDirectoryExists();
    }

    /**
     * Ensure the file cache data directory exists so ThrottleRequests (rate limiting)
     * does not throw "No such file or directory" and cause 500s.
     */
    protected function ensureCacheDataDirectoryExists(): void
    {
        $dir = storage_path('framework/cache/data');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
