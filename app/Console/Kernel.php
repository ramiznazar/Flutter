<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily tasks reset - run daily at midnight
        $schedule->command('tasks:daily-reset')
            ->daily()
            ->at('00:00');
        
        // Task rewards distribution - run every 5 minutes
        $schedule->command('tasks:distribute-rewards')
            ->everyFiveMinutes();
        
        // Update mining balances - run every 30 seconds for real-time updates
        $schedule->command('mining:update-balances')
            ->everyThirtySeconds()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
