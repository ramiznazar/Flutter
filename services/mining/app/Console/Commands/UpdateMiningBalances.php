<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserLevel;
use App\Models\Level;
use App\Models\UserBooster;
use App\Models\Setting;
use App\Models\CoinSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update mining balances for all active miners (runs every 30 seconds).
 * When mining service is enabled, run this in the mining service only; do not run in monolith.
 * Balance/token are never read from cache — always from MySQL.
 */
class UpdateMiningBalances extends Command
{
    protected $signature = 'mining:update-balances';

    protected $description = 'Update mining balances for all active miners (runs every 30 seconds)';

    public function handle(): int
    {
        $now = Carbon::now();
        $chunkSize = 500;
        $successCount = 0;
        $completedCount = 0;
        $errorCount = 0;

        $settings = Setting::first();
        $overallMiningSpeed = $settings ? (float) $settings->mining_speed : 10.00;
        $coinSettings = CoinSetting::first();

        if (!$coinSettings) {
            $this->error('Coin settings not found');
            return self::FAILURE;
        }

        User::where('is_mining', 1)
            ->where('account_status', 'active')
            ->whereNotNull('mining_end_time')
            ->chunk($chunkSize, function ($users) use (&$successCount, &$completedCount, &$errorCount, $now, $overallMiningSpeed, $coinSettings) {
                $updates = [];
                $completedUsers = [];

                foreach ($users as $user) {
                    try {
                        if ($user->mining_start_balance === null) {
                            $user->update(['mining_start_balance' => (float) $user->token]);
                            $user->refresh();
                        }

                        $miningEndTime = null;
                        try {
                            $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
                        } catch (\Exception $e) {
                            try {
                                $miningEndTime = Carbon::parse($user->mining_end_time);
                            } catch (\Exception $e2) {
                                Log::error("Invalid mining_end_time format for user {$user->id}: {$user->mining_end_time}");
                                $errorCount++;
                                continue;
                            }
                        }

                        if ($now->gt($miningEndTime)) {
                            $this->completeMiningSession($user, $now, $overallMiningSpeed, $coinSettings);
                            $completedUsers[] = $user->id;
                            $completedCount++;
                            continue;
                        }

                        $balanceData = $this->calculateMiningBalance($user, $now, $overallMiningSpeed, $coinSettings);
                        if ($balanceData) {
                            $updates[$user->id] = $balanceData;
                        }
                    } catch (\Exception $e) {
                        Log::error("Error processing user {$user->id} in UpdateMiningBalances: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                        $errorCount++;
                    }
                }

                if (!empty($updates)) {
                    try {
                        DB::beginTransaction();
                        $updateChunks = array_chunk($updates, 200, true);
                        foreach ($updateChunks as $chunk) {
                            $caseStatements = [];
                            $userIds = [];
                            foreach ($chunk as $userId => $data) {
                                $caseStatements[] = "WHEN " . (int) $userId . " THEN " . (float) $data['balance'];
                                $userIds[] = (int) $userId;
                            }
                            if (!empty($caseStatements)) {
                                $caseSql = "CASE id " . implode(' ', $caseStatements) . " END";
                                $ids = implode(',', $userIds);
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

                if (!empty($completedUsers)) {
                    User::whereIn('id', $completedUsers)->update([
                        'is_mining' => 0,
                        'mining_end_time' => null,
                        'mining_time' => 0,
                        'mining_start_balance' => null,
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

        return self::SUCCESS;
    }

    private function calculateMiningBalance($user, $now, $overallMiningSpeed, $coinSettings): ?array
    {
        try {
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
            $userCustomSpeed = $user->custom_coin_speed ?? null;
            $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
            $tokenPerSec = (float) $effectiveMiningSpeed / 3600;

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

            try {
                $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
            } catch (\Exception $e) {
                $miningEndTime = Carbon::parse($user->mining_end_time);
            }

            $elapsedSeconds = $now->diffInSeconds($miningEndTime);
            $totalMiningSeconds = (int) ($user->mining_time ?? $timeLimitInSec);
            $elapsedMiningSeconds = $totalMiningSeconds - $elapsedSeconds;
            if ($elapsedMiningSeconds < 0) {
                $elapsedMiningSeconds = 0;
            }

            if ($user->mining_start_balance === null) {
                $user->update(['mining_start_balance' => (float) $user->token]);
            }
            $startingBalance = (float) ($user->mining_start_balance ?? $user->token);
            $newBalance = $startingBalance + ($tokenPerSec * $elapsedMiningSeconds);
            if ($newBalance < $startingBalance) {
                $newBalance = $startingBalance;
            }

            return [
                'balance' => $newBalance,
                'token_per_sec' => $tokenPerSec,
                'multiplier' => $multiplier,
            ];
        } catch (\Exception $e) {
            Log::error("Error calculating balance for user {$user->id}: " . $e->getMessage());
            return null;
        }
    }

    private function completeMiningSession($user, $now, $overallMiningSpeed, $coinSettings): void
    {
        try {
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
            $userCustomSpeed = $user->custom_coin_speed ?? null;
            $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
            $tokenPerSec = (float) $effectiveMiningSpeed / 3600;

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

            $actualMiningTime = (int) ($user->mining_time ?? $timeLimitInSec);
            $tokensEarned = $tokenPerSec * $actualMiningTime;
            $startingBalance = $user->mining_start_balance !== null
                ? (float) $user->mining_start_balance
                : (float) $user->token;
            $finalBalance = $startingBalance + $tokensEarned;

            $user->update([
                'token' => $finalBalance,
                'is_mining' => 0,
                'mining_end_time' => null,
                'mining_time' => 0,
                'mining_start_balance' => null,
            ]);

            UserLevel::where('user_id', $user->id)->increment('mining_session');
        } catch (\Exception $e) {
            Log::error("Error completing mining session for user {$user->id}: " . $e->getMessage());
        }
    }
}
