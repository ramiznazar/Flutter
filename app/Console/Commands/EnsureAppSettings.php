<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class EnsureAppSettings extends Command
{
    protected $signature = 'app:ensure-settings
                            {--allow-app : Set maintenance=0 and force_update=0 so the Flutter app is not blocked }';

    protected $description = 'Ensure settings row (id=1) exists. Use --allow-app to clear maintenance/force_update so the app can run.';

    public function handle(): int
    {
        $allowApp = $this->option('allow-app');

        $settings = Setting::first();
        if (!$settings) {
            Setting::updateOrCreateSettings([]);
            $this->info('Created default settings row (id=1). App will not be blocked by missing config.');
            return 0;
        }

        if ($allowApp) {
            Setting::updateOrCreateSettings([
                'maintenance' => '0',
                'force_update' => '0',
            ]);
            $this->info('Set maintenance=0 and force_update=0. Flutter app should no longer show maintenance or force-update screens.');
        } else {
            $this->info('Settings row exists. Run with --allow-app to set maintenance=0 and force_update=0 if the app is blocked.');
        }

        return 0;
    }
}
