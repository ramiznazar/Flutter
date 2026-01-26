<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Level;
use App\Models\UserBooster;
use App\Models\Setting;
use App\Models\CoinSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMiningBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mining:update-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update mining balances for all active miners (runs every 30 seconds)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $chunkSize = 500;
        $successCount = 0;
        $completedCount = 0;
        $errorCount = 0;

        // Get settings once
        $settings = Setting::first();
        $overallMiningSpeed = $settings ? (float) $settings->mining_speed : 10.00;
        $coinSettings = CoinSetting::first();
        
        if (!$coinSettings) {
            $this->error('Coin settings not found');
            return;
        }

        // Process active miners in chunks
        // Also fix miners who don't have mining_start_balance set (for existing miners)
        User::where('is_mining', 1)
            ->where('account_status', 'active')
            ->whereNotNull('mining_end_time')
            ->chunk($chunkSize, function ($users) use (&$successCount, &$completedCount, &$errorCount, $now, $overallMiningSpeed, $coinSettings) {
                
                $updates = [];
                $completedUsers = [];
                
                foreach ($users as $user) {
                    try {
                        // Fix mining_start_balance if NULL (for existing miners)
                        if ($user->mining_start_balance === null) {
                            $user->update(['mining_start_balance' => (float) $user->token]);
                            $user->refresh();
                        }
                        
                        // Parse mining_end_time - handle both formats
                        $miningEndTime = null;
                        try {
                            $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
                        } catch (\Exception $e) {
                            // Try alternative format
                            try {
                                $miningEndTime = Carbon::parse($user->mining_end_time);
                            } catch (\Exception $e2) {
                                Log::error("Invalid mining_end_time format for user {$user->id}: {$user->mining_end_time}");
                                $errorCount++;
                                continue;
                            }
                        }
                        
                        // Check if mining session is completed
                        if ($now->gt($miningEndTime)) {
                            // Mining completed - calculate final balance and add to token
                            $this->completeMiningSession($user, $now, $overallMiningSpeed, $coinSettings);
                            $completedUsers[] = $user->id;
                            $completedCount++;
                            continue;
                        }
                        
                        // Mining still active - calculate current balance
                        $balanceData = $this->calculateMiningBalance($user, $now, $overallMiningSpeed, $coinSettings);
                        
                        if ($balanceData) {
                            // Always update balance, even if it's the same (to ensure it's current)
                            $updates[$user->id] = $balanceData;
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("Error processing user {$user->id} in UpdateMiningBalances: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                        $errorCount++;
                    }
                }
                
                // Bulk update balances
                if (!empty($updates)) {
                    try {
                        DB::beginTransaction();
                        
                        $updateChunks = array_chunk($updates, 200, true);
                        foreach ($updateChunks as $chunk) {
                            $caseStatements = [];
                            $userIds = [];
                            
                            foreach ($chunk as $userId => $data) {
                                $caseStatements[] = "WHEN " . (int)$userId . " THEN " . (float)$data['balance'];
                                $userIds[] = (int)$userId;
                            }
                            
                            if (!empty($caseStatements)) {
                                $caseSql = "CASE id " . implode(' ', $caseStatements) . " END";
                                $ids = implode(',', $userIds);
                                // Update token balance (users table doesn't have updated_at column)
                                DB::statement("UPDATE users SET token = {$caseSql} WHERE id IN ({$ids})");
                            }
                        }
                        
                        DB::commit();
                        $successCount += count($updates);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error in bulk balance update: " . $e->getMessage());
                        $errorCount += count($updates);
                    }
                }
                
                // Mark completed sessions
                if (!empty($completedUsers)) {
                    User::whereIn('id', $completedUsers)->update([
                        'is_mining' => 0,
                        'mining_end_time' => null,
                        'mining_time' => 0,
                        'mining_start_balance' => null
                    ]);
                }
            });

        if ($successCount > 0 || $completedCount > 0) {
            $this->info("Updated {$successCount} active miners, completed {$completedCount} sessions");
            Log::info("UpdateMiningBalances: Updated {$successCount} active miners, completed {$completedCount} sessions");
        } else {
            $this->info("No active miners to update");
        }
        
        if ($errorCount > 0) {
            $this->warn("Encountered {$errorCount} errors");
            Log::warning("UpdateMiningBalances: Encountered {$errorCount} errors");
        }
    }

    /**
     * Calculate current mining balance for a user
     */
    private function calculateMiningBalance($user, $now, $overallMiningSpeed, $coinSettings)
    {
        try {
            // Get user perks
            $userLevel = UserLevel::where('user_id', $user->id)->with('level')->first();
            
            if (!$userLevel || !$userLevel->level) {
                $firstLevel = Level::orderBy('id')->first();
                if (!$firstLevel) {
                    return null;
                }
                $perkCrutoxPerTime = (float) $firstLevel->perk_crutox_per_time;
                $perkMiningTime = (int) $firstLevel->perk_mining_time;
            } else {
                $perkCrutoxPerTime = (float) $userLevel->level->perk_crutox_per_time;
                $perkMiningTime = (int) $userLevel->level->perk_mining_time;
            }
            
            $timeLimitInSec = $perkMiningTime * 3600;
            
            // Apply custom speed
            $userCustomSpeed = $user->custom_coin_speed ?? null;
            $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
            
            // Calculate token_per_sec directly from mining speed (coins per hour)
            // mining_speed represents coins per hour, so divide by 3600 to get coins per second
            $tokenPerSec = (float) $effectiveMiningSpeed / 3600;
            
            // Get active booster multiplier
            $booster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('expires_at', '>', $now)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $multiplier = 1.0;
            if ($booster) {
                $boosterType = $booster->booster_type; // "2x", "3x", "5x"
                $multiplier = (float) str_replace('x', '', $boosterType); // 2, 3, or 5
            }
            
            // Apply booster multiplier
            $tokenPerSec = $tokenPerSec * $multiplier;
            
            // Calculate elapsed time - handle date format
            try {
                $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
            } catch (\Exception $e) {
                $miningEndTime = Carbon::parse($user->mining_end_time);
            }
            
            $elapsedSeconds = $now->diffInSeconds($miningEndTime);
            $totalMiningSeconds = (int) ($user->mining_time ?? $timeLimitInSec);
            $elapsedMiningSeconds = $totalMiningSeconds - $elapsedSeconds;
            
            // Ensure elapsedMiningSeconds is not negative
            if ($elapsedMiningSeconds < 0) {
                $elapsedMiningSeconds = 0;
            }
            
            // Get starting balance (use mining_start_balance if set, otherwise current token)
            // If mining_start_balance is NULL, set it to current token and save it
            if ($user->mining_start_balance === null) {
                $startingBalance = (float) $user->token;
                // Update the user to set mining_start_balance for future calculations
                $user->update(['mining_start_balance' => $startingBalance]);
            } else {
                $startingBalance = (float) $user->mining_start_balance;
            }
            
            // Calculate new balance
            $newBalance = $startingBalance + ($tokenPerSec * $elapsedMiningSeconds);
            
            // Ensure balance doesn't go below starting balance
            if ($newBalance < $startingBalance) {
                $newBalance = $startingBalance;
            }
            
            // Log calculation for debugging (only for first few users to avoid spam)
            if (rand(1, 100) === 1) {
                Log::info("Balance calculation for user {$user->id}: starting={$startingBalance}, tokenPerSec={$tokenPerSec}, elapsed={$elapsedMiningSeconds}, newBalance={$newBalance}");
            }
            
            return [
                'balance' => $newBalance,
                'token_per_sec' => $tokenPerSec,
                'multiplier' => $multiplier
            ];
            
        } catch (\Exception $e) {
            Log::error("Error calculating balance for user {$user->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Complete mining session and add final balance
     */
    private function completeMiningSession($user, $now, $overallMiningSpeed, $coinSettings)
    {
        try {
            // Get user perks
            $userLevel = UserLevel::where('user_id', $user->id)->with('level')->first();
            
            if (!$userLevel || !$userLevel->level) {
                $firstLevel = Level::orderBy('id')->first();
                if (!$firstLevel) {
                    return;
                }
                $perkCrutoxPerTime = (float) $firstLevel->perk_crutox_per_time;
                $perkMiningTime = (int) $firstLevel->perk_mining_time;
            } else {
                $perkCrutoxPerTime = (float) $userLevel->level->perk_crutox_per_time;
                $perkMiningTime = (int) $userLevel->level->perk_mining_time;
            }
            
            $timeLimitInSec = $perkMiningTime * 3600;
            
            // Apply custom speed
            $userCustomSpeed = $user->custom_coin_speed ?? null;
            $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
            
            // Calculate token_per_sec directly from mining speed (coins per hour)
            // mining_speed represents coins per hour, so divide by 3600 to get coins per second
            $tokenPerSec = (float) $effectiveMiningSpeed / 3600;
            
            // Get active booster (check at completion time)
            $booster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('expires_at', '>', $now)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $multiplier = 1.0;
            if ($booster) {
                $boosterType = $booster->booster_type;
                $multiplier = (float) str_replace('x', '', $boosterType);
            }
            
            $tokenPerSec = $tokenPerSec * $multiplier;
            
            // Calculate final tokens earned
            $actualMiningTime = (int) ($user->mining_time ?? $timeLimitInSec);
            $tokensEarned = $tokenPerSec * $actualMiningTime;
            
            // Get starting balance
            $startingBalance = $user->mining_start_balance !== null 
                ? (float) $user->mining_start_balance 
                : (float) $user->token;
            
            // Add final balance
            $finalBalance = $startingBalance + $tokensEarned;
            
            // Update user
            $user->update([
                'token' => $finalBalance,
                'is_mining' => 0,
                'mining_end_time' => null,
                'mining_time' => 0,
                'mining_start_balance' => null
            ]);
            
            // Increase mining level
            UserLevel::where('user_id', $user->id)->increment('mining_session');
            
        } catch (\Exception $e) {
            Log::error("Error completing mining session for user {$user->id}: " . $e->getMessage());
        }
    }
}
