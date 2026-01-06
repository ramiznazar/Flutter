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

        $secondsPerCoin = (int) $coinSettings->seconds_per_coin;
        $maxSecondsAllow = (int) $coinSettings->max_seconds_allow;
        $addToken = $perkCrutoxPerTime;
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
                    'usdt' => $usdt,
                    'total_mining_time_in_sec' => $timeLimitInSec
                ]);
            } else {
                // Still mining - calculate elapsed time for progress
                $elapsedSeconds = $now->diffInSeconds($miningEndTime);
                $totalMiningSeconds = (int) ($user->mining_time ?? $timeLimitInSec);
                $elapsedMiningSeconds = $totalMiningSeconds - $elapsedSeconds;
                
                // Get starting balance (stored when mining started)
                $startingBalance = (float) $user->token;
                
                // ✅ If frontend balance is provided (when reason == "get"), use it
                if ($request->reason === 'get' && $frontendBalance !== null && $frontendBalance >= 0) {
                    // Validate balance is reasonable (not more than 10x starting balance)
                    $maxExpectedBalance = $startingBalance + ($addToken * $totalMiningSeconds * 10);
                    if ($frontendBalance <= $maxExpectedBalance) {
                        // Accept and store frontend-calculated balance
                        $user->update(['token' => $frontendBalance]);
                        $currentBalance = $frontendBalance;
                    } else {
                        // Balance seems unreasonable, use server calculation
                        $tokensEarned = $addToken * $elapsedMiningSeconds;
                        $currentBalance = $startingBalance + $tokensEarned;
                    }
                } else {
                    // Fallback to server calculation (backward compatibility)
                    $tokensEarned = $addToken * $elapsedMiningSeconds;
                    $currentBalance = $startingBalance + $tokensEarned;
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'in_progress',
                    'server_time' => $currentTime,
                    'mining_end_time' => $user->mining_end_time ?? '',
                    'total_team' => (string) $user->total_invite,
                    'coin' => $user->coin,
                    'balance' => (string) $currentBalance, // ✅ Use frontend balance or calculated
                    'starting_balance' => (string) $startingBalance, // Balance when mining started
                    'token_per_sec' => $addToken,
                    'usdt' => $usdt,
                    'total_mining_time_in_sec' => $totalMiningSeconds,
                    'seconds_remaining' => $elapsedSeconds,
                    'elapsed_seconds' => $elapsedMiningSeconds
                ]);
            }
        }

        // Handle "get" reason
        if ($request->reason === 'get') {
            // ✅ If frontend balance is provided, accept and store it
            if ($frontendBalance !== null && $frontendBalance >= 0) {
                // Validate balance is reasonable
                $currentToken = (float) $user->token;
                $maxExpectedBalance = $currentToken * 10; // Allow up to 10x current balance
                
                if ($frontendBalance <= $maxExpectedBalance) {
                    // Accept and store frontend-calculated balance
                    $user->update(['token' => $frontendBalance]);
                    $balance = $frontendBalance;
                } else {
                    // Balance seems unreasonable, use current token
                    $balance = $currentToken;
                }
            } else {
                // No frontend balance provided, use current token (backward compatibility)
                $balance = (float) $user->token;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'idle',
                'server_time' => $currentTime,
                'mining_end_time' => $user->mining_end_time ?? '',
                'total_team' => (string) $user->total_invite,
                'coin' => $user->coin,
                'balance' => (string) $balance, // ✅ Use frontend balance or current token
                'token_per_sec' => $addToken,
                'usdt' => $usdt,
                'total_mining_time_in_sec' => $timeLimitInSec
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
        // The token field stores the current balance, which becomes the starting balance
        $startingBalance = (float) $user->token;
        // Note: We don't overwrite token here - it already contains the current balance
        // The token field will be updated by frontend during mining, or calculated server-side

        DB::beginTransaction();

        try {
            $user->update([
                'coin' => $newCoins,
                'is_mining' => 1,
                'mining_end_time' => $miningEndTime,
                'mining_time' => $totalTime
                // ✅ token field keeps current balance (which is the starting balance for this session)
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
                'balance' => (string) $startingBalance, // ✅ Starting balance (current balance when mining started)
                'starting_balance' => (string) $startingBalance, // ✅ For consistency
                'token_per_sec' => $addToken,
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
}
