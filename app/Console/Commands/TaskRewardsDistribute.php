<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\SocialMediaSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskRewardsDistribute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:distribute-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically distribute rewards for one-time tasks after 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        $completions = TaskCompletion::where('task_type', 'onetime')
            ->where('reward_claimed', 0)
            ->where('reward_available_at', '<=', $now)
            ->with(['task', 'user'])
            ->get();

        $distributed = 0;
        $errors = 0;

        if ($completions->count() > 0) {
            DB::beginTransaction();
            
            try {
                foreach ($completions as $completion) {
                    $reward = (float) $completion->task->Token;
                    
                    // Mark as claimed
                    $completion->update([
                        'reward_claimed' => 1,
                        'reward_claimed_at' => $now
                    ]);
                    
                    // Add reward to user
                    $user = $completion->user;
                    $user->increment('token', $reward);
                    
                    $distributed++;
                }
                
                DB::commit();
                $this->info("Distributed rewards for $distributed one-time tasks.");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error distributing rewards: " . $e->getMessage());
                $errors = $completions->count();
            }
        } else {
            $this->info("No rewards to distribute.");
        }
    }
}
