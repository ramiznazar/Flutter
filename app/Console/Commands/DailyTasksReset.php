<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\TaskCompletion;
use Carbon\Carbon;

class DailyTasksReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:daily-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily tasks every 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = Setting::first();
        $lastResetTime = $settings ? $settings->daily_tasks_reset_time : null;
        
        $now = Carbon::now();
        $shouldReset = false;

        if ($lastResetTime === null) {
            $shouldReset = true;
        } else {
            $lastReset = Carbon::parse($lastResetTime);
            $hoursSinceReset = $now->diffInHours($lastReset);
            
            if ($hoursSinceReset >= 24) {
                $shouldReset = true;
            }
        }

        if ($shouldReset) {
            // Mark all daily task completions as expired
            TaskCompletion::where('task_type', 'daily')
                ->where('reward_claimed', 0)
                ->where('reward_available_at', '<', $now)
                ->update(['reward_claimed' => 1]);
            
            // Update reset time
            if ($settings) {
                $settings->update(['daily_tasks_reset_time' => $now]);
            } else {
                Setting::create(['daily_tasks_reset_time' => $now]);
            }
            
            $this->info("Daily tasks reset successfully at " . $now->format('Y-m-d H:i:s'));
        } else {
            $nextReset = Carbon::parse($lastResetTime)->addHours(24);
            $hoursUntilReset = $now->diffInHours($nextReset);
            $this->info("Daily tasks reset not needed. Next reset in " . round($hoursUntilReset, 2) . " hours.");
        }
    }
}
