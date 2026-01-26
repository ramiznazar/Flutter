<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Level;
use App\Models\CoinSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MiningController extends Controller
{
    private function getUserPerks($userId)
    {
        $userLevel = UserLevel::where('user_id', $userId)
            ->with('level')
            ->first();

        if (!$userLevel || !$userLevel->level) {
            // Get first level as default
            $firstLevel = Level::orderBy('id')->first();
            if (!$firstLevel) {
                return [
                    'current_level' => 1,
                    'perk_crutox_per_time' => 0.5,
                    'perk_mining_time' => 12
                ];
            }
            return [
                'current_level' => $firstLevel->id,
                'perk_crutox_per_time' => (float) $firstLevel->perk_crutox_per_time,
                'perk_mining_time' => (int) $firstLevel->perk_mining_time
            ];
        }

        return [
            'current_level' => $userLevel->current_level,
            'perk_crutox_per_time' => (float) $userLevel->level->perk_crutox_per_time,
            'perk_mining_time' => (int) $userLevel->level->perk_mining_time
        ];
    }

    private function checkRecord($userId)
    {
        $userLevel = UserLevel::where('user_id', $userId)->first();
        
        if (!$userLevel) {
            $firstLevel = Level::orderBy('id')->first();
            if ($firstLevel) {
                UserLevel::create([
                    'user_id' => $userId,
                    'mining_session' => 0,
                    'spin_wheel' => 0,
                    'current_level' => $firstLevel->id,
                    'achieved_at' => now()->format('Y-m-d H:i:s')
                ]);
            }
        }
    }

    private function increaseUserMiningLevel($userId)
    {
        $this->checkRecord($userId);
        UserLevel::where('user_id', $userId)->increment('mining_session');
    }

    public function startMining(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'coins' => 'required|numeric|min:0',
            'reason' => 'sometimes|string',
            'balance' => 'sometimes|numeric|min:0', // ✅ Accept frontend-calculated balance
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?? 'Missing required fields'
            ], 400);
        }
        
        // Convert coins to integer
        $request->merge(['coins' => (int) $request->coins]);
        
        // Get frontend balance if provided
        $frontendBalance = $request->has('balance') && $request->balance !== null 
            ? (float) $request->balance 
            : null;

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $this->checkRecord($user->id);
        $perks = $this->getUserPerks($user->id);

        $timeLimitInSec = $perks['perk_mining_time'] * 3600;
        $perkCrutoxPerTime = (float) $perks['perk_crutox_per_time'] / $timeLimitInSec;
        $perkCrutoxPerTime = number_format($perkCrutoxPerTime, 10, '.', '');

        $currentTime = Carbon::now()->format('Y-m-d-H:i:s');

        $coinSettings = CoinSetting::first();
        if (!$coinSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Coin settings not found'
            ], 404);
        }

        // Get settings for mining speed calculation
        $settings = \App\Models\Setting::first();
        $overallMiningSpeed = $settings ? (float) $settings->mining_speed : 10.00;
        
        // Check if user has custom coin speed
        $userCustomSpeed = $user->custom_coin_speed ?? null;
        $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
        
        // Calculate token_per_sec directly from mining speed (coins per hour)
        // mining_speed represents coins per hour, so divide by 3600 to get coins per second
        $addToken = (float) $effectiveMiningSpeed / 3600;
        $addToken = number_format($addToken, 10, '.', '');

        $secondsPerCoin = (int) $coinSettings->seconds_per_coin;
        $maxSecondsAllow = (int) $coinSettings->max_seconds_allow;
        $usdt = (float) $coinSettings->token_price;

        // Check if user is mining
        if ($user->is_mining == 1 && $user->mining_end_time) {
            $miningEndTime = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
            $now = Carbon::now();

            if ($now->gt($miningEndTime)) {
                // Mining finished, calculate and add tokens
                // Use the actual mining_time stored in database, not timeLimitInSec
                $actualMiningTime = (int) ($user->mining_time ?? $timeLimitInSec);
                $token = (float) $user->token;
                $token += $addToken * $actualMiningTime;

                $user->update([
                    'token' => $token,
                    'is_mining' => 0,
                    'mining_end_time' => null,
                    'mining_time' => 0
                ]);

                $this->increaseUserMiningLevel($user->id);
                
                // Return updated status after completion
                return response()->json([
                    'success' => true,
                    'message' => 'idle',
                    'server_time' => $currentTime,
                    'mining_end_time' => '',
                    'total_team' => (string) $user->total_invite,
                    'coin' => $user->coin,
                    'balance' => (string) $user->fresh()->token, // Get updated token
                    'token_per_sec' => $addToken,
                    'mining_speed' => (float) $effectiveMiningSpeed, // Return effective mining speed
                    'usdt' => $usdt,
                    'total_mining_time_in_sec' => $timeLimitInSec
                ]);
            } else {
                // Still mining - calculate elapsed time for progress
                $elapsedSeconds = $now->diffInSeconds($miningEndTime);
                $totalMiningSeconds = (int) ($user->mining_time ?? $timeLimitInSec);
                $elapsedMiningSeconds = $totalMiningSeconds - $elapsedSeconds;
                
                // Get current server balance
                $currentServerBalance = (float) $user->token;
                
                // Calculate starting balance (balance when mining started)
                // We estimate it by subtracting expected mined tokens from current balance
                // This is an approximation since we don't store starting balance separately
                $expectedMinedTokens = $addToken * $elapsedMiningSeconds;
                $estimatedStartingBalance = max(0, $currentServerBalance - $expectedMinedTokens);
                
                // ✅ If frontend balance is provided (when reason == "get"), accept and store it
                if ($request->reason === 'get' && $frontendBalance !== null && $frontendBalance >= 0) {
                    // Accept frontend balance if it's higher or equal to current balance
                    // This handles admin-given coins and real-time increments
                    if ($frontendBalance >= $currentServerBalance) {
                        // Store the frontend balance as the new current balance
                        $user->update(['token' => $frontendBalance]);
                        $currentBalance = $frontendBalance;
                        // Recalculate starting balance based on new balance
                        $estimatedStartingBalance = max(0, $frontendBalance - $expectedMinedTokens);
                    } else {
                        // Frontend balance is lower (shouldn't happen, but keep server balance)
                        // Use server calculation instead
                        $tokensEarned = $addToken * $elapsedMiningSeconds;
                        $currentBalance = $currentServerBalance + $tokensEarned;
                        // Update with calculated balance
                        $user->update(['token' => $currentBalance]);
                    }
                } else {
                    // Fallback to server calculation (backward compatibility)
                    $tokensEarned = $addToken * $elapsedMiningSeconds;
                    $currentBalance = $currentServerBalance + $tokensEarned;
                    // Update with calculated balance
                    $user->update(['token' => $currentBalance]);
                }
                
                $startingBalance = $estimatedStartingBalance;
                
                return response()->json([
                    'success' => true,
                    'message' => 'in_progress',
                    'server_time' => $currentTime,
                    'mining_end_time' => $user->mining_end_time ?? '',
                    'total_team' => (string) $user->total_invite,
                    'coin' => $user->coin,
                    'balance' => number_format($currentBalance, 10, '.', ''), // ✅ Use frontend balance or calculated with 10 decimal precision
                    'starting_balance' => number_format($startingBalance, 10, '.', ''), // Balance when mining started
                    'token_per_sec' => $addToken,
                    'mining_speed' => (float) $effectiveMiningSpeed, // Return effective mining speed
                    'usdt' => $usdt,
                    'total_mining_time_in_sec' => $totalMiningSeconds,
                    'seconds_remaining' => $elapsedSeconds,
                    'elapsed_seconds' => $elapsedMiningSeconds
                ]);
            }
        }

        // Handle "get" reason (sync request)
        if ($request->reason === 'get') {
            // ✅ If frontend balance is provided, accept and store it
            if ($frontendBalance !== null && $frontendBalance >= 0) {
                $currentToken = (float) $user->token;
                
                // Accept frontend balance if it's higher or equal to current balance
                // This handles admin-given coins and real-time increments
                if ($frontendBalance >= $currentToken) {
                    // Store the frontend balance as the new current balance
                    $user->update(['token' => $frontendBalance]);
                    $balance = $frontendBalance;
                } else {
                    // Frontend balance is lower (shouldn't happen, but keep server balance)
                    $balance = $currentToken;
                }
            } else {
                // No frontend balance provided, use current token (backward compatibility)
                $balance = (float) $user->token;
            }
            
            // Get starting balance (for reference - this is the balance when mining started)
            // If mining is active, starting_balance is the balance at mining start
            // If idle, starting_balance is the current balance
            $startingBalance = $user->is_mining == 1 && $user->mining_end_time 
                ? (float) $user->token // This should ideally be stored separately, but using token for now
                : (float) $balance;
            
            return response()->json([
                'success' => true,
                'message' => $user->is_mining == 1 ? 'in_progress' : 'idle',
                'server_time' => $currentTime,
                'mining_end_time' => $user->mining_end_time ?? '',
                'total_team' => (string) $user->total_invite,
                'coin' => $user->coin,
                'balance' => number_format($balance, 10, '.', ''), // ✅ Return stored balance with 10 decimal precision
                'starting_balance' => number_format($startingBalance, 10, '.', ''), // Balance when mining started
                'token_per_sec' => $addToken,
                'mining_speed' => (float) $effectiveMiningSpeed, // Return effective mining speed
                'usdt' => $usdt,
                'total_mining_time_in_sec' => $timeLimitInSec,
                'seconds_remaining' => $user->is_mining == 1 && $user->mining_end_time 
                    ? Carbon::now()->diffInSeconds(Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time))
                    : 0,
                'elapsed_seconds' => $user->is_mining == 1 && $user->mining_end_time
                    ? (int) ($user->mining_time ?? $timeLimitInSec) - Carbon::now()->diffInSeconds(Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time))
                    : 0
            ]);
        }

        // Start mining
        $coins = (int) $user->coin;
        $coinsRequired = (int) $request->coins;

        if ($coins < $coinsRequired) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient coins'
            ], 400);
        }

        // Calculate total time
        $totalTime = $coinsRequired * $secondsPerCoin;

        if ($totalTime > $maxSecondsAllow) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum mining time exceeded'
            ], 400);
        }

        // Deduct coins
        $newCoins = $coins - $coinsRequired;
        $totalTime = $totalTime + $timeLimitInSec;
        $miningEndTime = Carbon::now()->addSeconds($totalTime)->format('Y-m-d-H:i:s');
        
        // ✅ Get current balance (this will be the starting balance for this mining session)
        // If frontend balance is provided, use it; otherwise use current token
        $startingBalance = $frontendBalance !== null && $frontendBalance >= 0 
            ? $frontendBalance 
            : (float) $user->token;
        
        // Store starting balance separately for backend-managed mining
        // Backend will calculate balance from this starting point

        DB::beginTransaction();

        try {
            $user->update([
                'coin' => $newCoins,
                'is_mining' => 1,
                'mining_end_time' => $miningEndTime,
                'mining_time' => $totalTime,
                'token' => $startingBalance, // Current balance (will be updated by scheduled job)
                'mining_start_balance' => $startingBalance // Store starting balance for calculation
            ]);

            // Bonus to invited referral if coins_required == 0
            if ($coinsRequired == 0) {
                if ($user->invite_setup != "skip" && $user->invite_setup != "not_setup") {
                    $referrerId = is_numeric($user->invite_setup) 
                        ? (int) $user->invite_setup 
                        : User::where('username', $user->invite_setup)->value('id');
                    
                    if ($referrerId) {
                        $inSec = 12 * $secondsPerCoin;
                        $tmpToken = 0.1 * ($inSec * $addToken);
                        User::where('id', $referrerId)->increment('token', $tmpToken);
                    }
                }
            }

            $this->increaseUserMiningLevel($user->id);

            DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'in_progress',
                    'server_time' => $currentTime,
                    'mining_end_time' => $miningEndTime,
                    'total_team' => (string) $user->total_invite,
                    'coin' => $newCoins,
                    'balance' => number_format($startingBalance, 10, '.', ''), // ✅ Starting balance with 10 decimal precision
                    'starting_balance' => number_format($startingBalance, 10, '.', ''), // ✅ Balance when mining started
                    'token_per_sec' => $addToken,
                    'mining_speed' => (float) $effectiveMiningSpeed, // Return effective mining speed
                    'usdt' => $usdt,
                    'total_mining_time_in_sec' => $totalTime,
                    'seconds_remaining' => $totalTime,
                    'elapsed_seconds' => 0
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to start mining: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startCoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reason' => 'required|in:get,start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $coinSettings = CoinSetting::first();
        if (!$coinSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Coin settings not found'
            ], 404);
        }

        $claimTimeInSec = (int) $coinSettings->claim_time_in_sec;
        $maxCoinClaimAllow = (int) $coinSettings->max_coin_claim_allow;
        $currentTime = Carbon::now()->format('Y-m-d-H:i:s');

        if ($request->reason === 'get') {
            $coinEndTime = $user->coin_end_time ? Carbon::parse($user->coin_end_time) : null;
            
            if ($coinEndTime && Carbon::now()->gt($coinEndTime)) {
                $user->update([
                    'total_coin_claim' => 0,
                    'coin_end_time' => null
                ]);
                $coinEndTime = null;
            }

            $progress = ($coinEndTime && Carbon::now()->lt($coinEndTime)) ? 'in_progress' : 'idle';

            return response()->json([
                'success' => true,
                'server_time' => $currentTime,
                'coin_end_time' => $user->coin_end_time ?? '0000-00-00 00:00:00',
                'total_coin_claim' => (int) $user->total_coin_claim,
                'progress' => $progress
            ]);
        } elseif ($request->reason === 'start') {
            $totalCoinClaim = (int) $user->total_coin_claim;
            $coin = (int) $user->coin;
            $coinEndTime = $user->coin_end_time ? Carbon::parse($user->coin_end_time) : null;

            // Increment
            $totalCoinClaim += 1;
            $coin += 1;

            // Check if time is over
            if (!$coinEndTime || Carbon::now()->gt($coinEndTime)) {
                $totalCoinClaim = 0;
                $coinEndTime = Carbon::now()->addSeconds($claimTimeInSec);
            } else {
                $coinEndTime = Carbon::parse($user->coin_end_time);
            }

            if ($totalCoinClaim >= $maxCoinClaimAllow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limit Exceeded',
                    'server_time' => $currentTime,
                    'coin_end_time' => $user->coin_end_time ?? '0000-00-00 00:00:00',
                    'total_coin_claim' => $totalCoinClaim,
                    'progress' => 'in_progress'
                ], 400);
            }

            $user->update([
                'coin_end_time' => $coinEndTime->format('Y-m-d H:i:s'),
                'total_coin_claim' => $totalCoinClaim,
                'coin' => $coin
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully claimed',
                'server_time' => $currentTime,
                'coin_end_time' => $coinEndTime->format('Y-m-d H:i:s'),
                'total_coin_claim' => $totalCoinClaim,
                'progress' => 'in_progress'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid reason'
        ], 400);
    }

    public function socialClaim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'ID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if already claimed
        $alreadyClaimed = \App\Models\SocialMediaToken::where('user_id', $user->id)
            ->where('social_media_id', $request->ID)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'success' => false,
                'message' => 'User has already claimed tokens for this social media ID.'
            ], 400);
        }

        $socialMedia = \App\Models\SocialMediaSetting::find($request->ID);

        if (!$socialMedia) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid social media ID.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Add tokens to user
            $user->increment('token', (float) $socialMedia->Token);

            // Record claim
            \App\Models\SocialMediaToken::create([
                'user_id' => $user->id,
                'social_media_id' => $request->ID,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tokens claimed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Token claim failed.'
            ], 500);
        }
    }

    public function socialList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $socialMediaList = \App\Models\SocialMediaSetting::leftJoin('social_media_tokens', function($join) use ($user) {
                $join->on('social_media_setting.id', '=', 'social_media_tokens.social_media_id')
                     ->where('social_media_tokens.user_id', '=', $user->id);
            })
            ->select('social_media_setting.*')
            ->selectRaw('CASE WHEN social_media_tokens.user_id IS NOT NULL THEN 1 ELSE 0 END AS claimed')
            ->get();

        return response()->json([
            'success' => true,
            'social_media_setting' => $socialMediaList
        ]);
    }

    public function claimBonus(Request $request)
    {
        // Claim bonus rewards
        return response()->json([
            'success' => true,
            'message' => 'Bonus claimed'
        ]);
    }

    public function bonusHistory(Request $request)
    {
        // Get bonus history
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Get daily reward status (check if user can claim)
     */
    public function getDailyRewardStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

        // Check if daily_reward_claims table exists and has data
        try {
            $lastClaim = DB::table('daily_reward_claims')
                ->where('user_id', $user->id)
                ->where('claimed_at', '>=', $twentyFourHoursAgo)
                ->orderBy('claimed_at', 'desc')
                ->first();

            if ($lastClaim) {
                $nextAvailableAt = Carbon::parse($lastClaim->claimed_at)->addHours(24);
                $secondsUntilAvailable = $now->diffInSeconds($nextAvailableAt);
                
                return response()->json([
                    'success' => true,
                    'claimed' => true,
                    'last_claimed_at' => $lastClaim->claimed_at,
                    'coins_claimed' => (float) $lastClaim->coins_claimed,
                    'next_available_at' => $nextAvailableAt->format('Y-m-d H:i:s'),
                    'seconds_until_available' => $secondsUntilAvailable
                ]);
            }
        } catch (\Exception $e) {
            // Table might not exist or have different structure - return available
            Log::warning('Daily reward claims table check failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'claimed' => false,
            'available' => true,
            'message' => 'Daily reward is available'
        ]);
    }

    /**
     * Add daily reward coins to user's wallet
     * User watches ad and gets 2-4 coins (frontend manages timer)
     * Enforces 24-hour cooldown between claims
     */
    public function addDailyReward(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'coins' => 'required|numeric|min:0|max:10', // Allow 0-10 coins for flexibility
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?? 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

        // Check if user claimed daily reward within last 24 hours
        try {
            $lastClaim = DB::table('daily_reward_claims')
                ->where('user_id', $user->id)
                ->where('claimed_at', '>=', $twentyFourHoursAgo)
                ->orderBy('claimed_at', 'desc')
                ->first();

            if ($lastClaim) {
                $nextAvailableAt = Carbon::parse($lastClaim->claimed_at)->addHours(24);
                $secondsUntilAvailable = $now->diffInSeconds($nextAvailableAt);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Daily reward already claimed. Available again in 24 hours.',
                    'next_available_at' => $nextAvailableAt->format('Y-m-d H:i:s'),
                    'seconds_until_available' => $secondsUntilAvailable
                ], 400);
            }
        } catch (\Exception $e) {
            // Table might not exist - log and continue (will create record)
            Log::warning('Daily reward claims table check failed: ' . $e->getMessage());
        }

        $coins = (float) $request->coins;

        // If coins not provided or 0, generate random 2-4 coins
        if ($coins <= 0) {
            $coins = round(rand(200, 400) / 100, 2); // Random between 2.00 and 4.00
        }

        DB::beginTransaction();

        try {
            // Add coins to mining balance (token)
            $user->increment('token', $coins);
            
            // If mining is active, adjust mining_start_balance so balance calculation continues correctly
            if ($user->is_mining == 1 && $user->mining_start_balance !== null) {
                $user->increment('mining_start_balance', $coins);
            }
            
            // Record the claim for 24-hour cooldown tracking
            try {
                DB::table('daily_reward_claims')->insert([
                    'user_id' => $user->id,
                    'coins_claimed' => $coins,
                    'claimed_at' => $now
                ]);
            } catch (\Exception $e) {
                // Table might not exist - log but don't fail
                Log::warning('Failed to record daily reward claim: ' . $e->getMessage());
            }
            
            $user->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily reward coins added successfully',
                'coins_added' => $coins,
                'new_balance' => (float) $user->token,
                'is_mining_active' => $user->is_mining == 1
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error adding coins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current mining status (for frontend polling)
     * This endpoint returns the current balance calculated by backend
     * Frontend should poll this every 5-10 seconds
     */
    public function miningStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?? 'Email is required'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $this->checkRecord($user->id);
        $perks = $this->getUserPerks($user->id);

        $timeLimitInSec = $perks['perk_mining_time'] * 3600;

        $currentTime = Carbon::now()->format('Y-m-d-H:i:s');

        $coinSettings = CoinSetting::first();
        if (!$coinSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Coin settings not found'
            ], 404);
        }

        // Get settings for mining speed calculation
        $settings = \App\Models\Setting::first();
        $overallMiningSpeed = $settings ? (float) $settings->mining_speed : 10.00;
        
        // Check if user has custom coin speed
        $userCustomSpeed = $user->custom_coin_speed ?? null;
        $effectiveMiningSpeed = $userCustomSpeed ?? $overallMiningSpeed;
        
        // Calculate token_per_sec directly from mining speed (coins per hour)
        // mining_speed represents coins per hour, so divide by 3600 to get coins per second
        $tokenPerSec = (float) $effectiveMiningSpeed / 3600;

        // Get active booster and apply multiplier
        $booster = \App\Models\UserBooster::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        $boosterMultiplier = 1.0;
        $hasActiveBooster = false;
        $boosterType = null;
        $boosterExpiresAt = null;
        $boosterSecondsRemaining = 0;

        if ($booster) {
            $hasActiveBooster = true;
            $boosterType = $booster->booster_type;
            $boosterMultiplier = (float) str_replace('x', '', $boosterType);
            $boosterExpiresAt = $booster->expires_at->format('Y-m-d H:i:s');
            $boosterSecondsRemaining = Carbon::now()->diffInSeconds($booster->expires_at);
            $tokenPerSec = $tokenPerSec * $boosterMultiplier;
        }

        $usdt = (float) $coinSettings->token_price;

        // Get current balance from database (updated by scheduled job)
        // IMPORTANT: Refresh the user model to get the latest token value from database
        // The scheduled job updates the token field, so we need fresh data
        $user->refresh();
        $currentBalance = (float) $user->token;
        $startingBalance = $user->mining_start_balance !== null 
            ? (float) $user->mining_start_balance 
            : $currentBalance;

        // Calculate mining status
        $isMining = $user->is_mining == 1 && $user->mining_end_time;
        $miningStatus = 'idle';
        $miningEndTime = null;
        $secondsRemaining = 0;
        $elapsedSeconds = 0;
        $totalMiningTimeInSec = 0;

        if ($isMining) {
            $miningStatus = 'in_progress';
            $miningEndTime = $user->mining_end_time;
            $miningEndTimeCarbon = Carbon::createFromFormat('Y-m-d-H:i:s', $user->mining_end_time);
            $now = Carbon::now();
            
            if ($now->gt($miningEndTimeCarbon)) {
                // Mining completed (scheduled job should handle this, but return status)
                $miningStatus = 'completed';
                $secondsRemaining = 0;
                $elapsedSeconds = (int) ($user->mining_time ?? $timeLimitInSec);
            } else {
                $secondsRemaining = $now->diffInSeconds($miningEndTimeCarbon);
                $totalMiningTimeInSec = (int) ($user->mining_time ?? $timeLimitInSec);
                $elapsedSeconds = $totalMiningTimeInSec - $secondsRemaining;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $miningStatus,
            'server_time' => $currentTime,
            'mining_end_time' => $miningEndTime ?? '',
            'total_team' => (string) $user->total_invite,
            'coin' => $user->coin,
            'balance' => number_format($currentBalance, 10, '.', ''), // Current balance from backend
            'starting_balance' => number_format($startingBalance, 10, '.', ''), // Balance when mining started
            'token_per_sec' => number_format($tokenPerSec, 10, '.', ''), // Token per second (with booster applied) - use this for smooth UI animation
            'mining_speed' => (float) $effectiveMiningSpeed,
            'usdt' => $usdt,
            'total_mining_time_in_sec' => $totalMiningTimeInSec,
            'seconds_remaining' => $secondsRemaining,
            'elapsed_seconds' => $elapsedSeconds,
            'has_active_booster' => $hasActiveBooster,
            'booster_type' => $boosterType,
            'booster_multiplier' => $boosterMultiplier,
            'booster_expires_at' => $boosterExpiresAt,
            'booster_seconds_remaining' => $boosterSecondsRemaining,
            'balance_timestamp' => Carbon::now()->toIso8601String(), // Timestamp when this balance was calculated - use for smooth animation
            'is_mining_active' => $isMining // Helper flag for frontend
        ]);
    }
}
