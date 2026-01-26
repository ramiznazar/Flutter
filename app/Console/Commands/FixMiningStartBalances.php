<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixMiningStartBalances extends Command
{
    protected $signature = 'mining:fix-start-balances';
    protected $description = 'One-time fix: Set mining_start_balance for existing active miners';

    public function handle()
    {
        $this->info('Fixing mining_start_balance for existing active miners...');
        
        $fixed = 0;
        
        // Get all active miners without mining_start_balance
        $users = User::where('is_mining', 1)
            ->where('account_status', 'active')
            ->whereNull('mining_start_balance')
            ->get();
        
        foreach ($users as $user) {
            // Set mining_start_balance to current token value
            $user->update(['mining_start_balance' => (float) $user->token]);
            $fixed++;
            $this->line("Fixed user {$user->id} (email: {$user->email}) - set mining_start_balance to {$user->token}");
        }
        
        $this->info("Fixed {$fixed} users.");
        
        // Also fix users where mining_start_balance is 0 but they've been mining
        $usersWithZero = User::where('is_mining', 1)
            ->where('account_status', 'active')
            ->where('mining_start_balance', 0)
            ->where('token', 0)
            ->get();
        
        $this->info("Found {$usersWithZero->count()} users with zero balance - these will be calculated from 0.");
        
        return 0;
    }
}
