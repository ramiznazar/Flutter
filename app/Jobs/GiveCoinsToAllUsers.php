<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GiveCoinsToAllUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coinAmount;
    public $timeout = 600; // 10 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(float $coinAmount)
    {
        $this->coinAmount = $coinAmount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failedCount = 0;
        $chunkSize = 500; // Increased chunk size for faster processing (500 users at a time)

        // Process users in reverse order (highest ID to lowest) in chunks to avoid memory issues
        User::where('account_status', 'active')
            ->orderBy('id', 'DESC') // Process from highest ID to lowest
            ->chunk($chunkSize, function ($users) use (&$successCount, &$failedCount) {
                // Prepare bulk update data
                $updates = [];
                $userIds = [];
                
                foreach ($users as $user) {
                    $userIds[] = $user->id;
                }
                
                // Get all current coin values in one query
                $currentCoins = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->pluck('coin', 'id')
                    ->toArray();
                
                // Get current token values for users
                $currentTokens = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->pluck('token', 'id')
                    ->toArray();
                
                // Get mining status for users
                $miningUsers = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->where('is_mining', 1)
                    ->whereNotNull('mining_start_balance')
                    ->pluck('mining_start_balance', 'id')
                    ->toArray();
                
                // Prepare updates for users where new coins >= 0
                $coinUpdates = [];
                $tokenUpdates = [];
                $miningBalanceUpdates = [];
                
                foreach ($users as $user) {
                    try {
                        $currentCoin = (float) ($currentCoins[$user->id] ?? 0);
                        $newCoins = $currentCoin + $this->coinAmount;

                        // Only update if new coins won't be negative
                        if ($newCoins >= 0) {
                            $coinUpdates[$user->id] = $newCoins;
                            
                            // Also update token (mining balance)
                            $currentToken = (float) ($currentTokens[$user->id] ?? 0);
                            $tokenUpdates[$user->id] = $currentToken + $this->coinAmount;
                            
                            // If user is mining, also update mining_start_balance
                            if (isset($miningUsers[$user->id])) {
                                $miningBalanceUpdates[$user->id] = (float) $miningUsers[$user->id] + $this->coinAmount;
                            }
                        } else {
                            $failedCount++;
                        }
                    } catch (\Exception $e) {
                        Log::error("Error preparing update for user {$user->id}: " . $e->getMessage());
                        $failedCount++;
                    }
                }
                
                // Perform bulk updates using CASE statement for maximum speed
                if (!empty($coinUpdates)) {
                    try {
                        DB::beginTransaction();
                        
                        // Update in batches to avoid query size limits
                        $updateChunks = array_chunk($coinUpdates, 200, true);
                        foreach ($updateChunks as $chunk) {
                            // Build CASE statement for coin updates
                            $coinCaseStatements = [];
                            $tokenCaseStatements = [];
                            $userIds = [];
                            
                            foreach ($chunk as $userId => $newCoins) {
                                $coinCaseStatements[] = "WHEN " . (int)$userId . " THEN " . (float)$newCoins;
                                $tokenCaseStatements[] = "WHEN " . (int)$userId . " THEN " . (float)$tokenUpdates[$userId];
                                $userIds[] = (int)$userId;
                            }
                            
                            if (!empty($coinCaseStatements)) {
                                $coinCaseSql = "CASE id " . implode(' ', $coinCaseStatements) . " END";
                                $tokenCaseSql = "CASE id " . implode(' ', $tokenCaseStatements) . " END";
                                $ids = implode(',', $userIds);
                                DB::statement("UPDATE users SET coin = {$coinCaseSql}, token = {$tokenCaseSql} WHERE id IN ({$ids})");
                            }
                        }
                        
                        // Update mining_start_balance for mining users
                        if (!empty($miningBalanceUpdates)) {
                            $miningChunks = array_chunk($miningBalanceUpdates, 200, true);
                            foreach ($miningChunks as $chunk) {
                                $miningCaseStatements = [];
                                $userIds = [];
                                
                                foreach ($chunk as $userId => $newBalance) {
                                    $miningCaseStatements[] = "WHEN " . (int)$userId . " THEN " . (float)$newBalance;
                                    $userIds[] = (int)$userId;
                                }
                                
                                if (!empty($miningCaseStatements)) {
                                    $miningCaseSql = "CASE id " . implode(' ', $miningCaseStatements) . " END";
                                    $ids = implode(',', $userIds);
                                    DB::statement("UPDATE users SET mining_start_balance = {$miningCaseSql} WHERE id IN ({$ids}) AND is_mining = 1");
                                }
                            }
                        }
                        
                        DB::commit();
                        $successCount += count($coinUpdates);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error in bulk coin update: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
                        // Fallback to individual updates if bulk fails
                        foreach ($updates as $userId => $newCoins) {
                            try {
                                DB::table('users')->where('id', $userId)->update(['coin' => $newCoins]);
                                $successCount++;
                            } catch (\Exception $e2) {
                                $failedCount++;
                            }
                        }
                    }
                }
            });

        Log::info("GiveCoinsToAllUsers completed: Success={$successCount}, Failed={$failedCount}, Amount={$this->coinAmount}");
        
        // Verify the job completed successfully
        $totalUsers = User::where('account_status', 'active')->count();
        if ($successCount + $failedCount < $totalUsers) {
            Log::warning("GiveCoinsToAllUsers: Only " . ($successCount + $failedCount) . " out of {$totalUsers} users were processed.");
        }
    }
}
