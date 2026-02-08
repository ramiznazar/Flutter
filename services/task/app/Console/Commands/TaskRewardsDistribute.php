<?php

namespace App\Console\Commands;

use App\Models\TaskCompletion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskRewardsDistribute extends Command
{
    protected $signature = 'tasks:distribute-rewards';

    protected $description = 'Automatically distribute rewards for one-time tasks after 1 hour';

    public function handle(): int
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

                    $completion->update([
                        'reward_claimed' => 1,
                        'reward_claimed_at' => $now,
                    ]);

                    $user = $completion->user;
                    $user->increment('token', $reward);

                    $distributed++;
                }

                DB::commit();
                $this->info("Distributed rewards for {$distributed} one-time tasks.");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error distributing rewards: ' . $e->getMessage());
                $errors = $completions->count();
            }
        } else {
            $this->info('No rewards to distribute.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
