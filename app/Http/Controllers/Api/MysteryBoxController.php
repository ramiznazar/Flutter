<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MysteryBoxClaim;
use App\Models\Setting;
use App\Models\UserBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MysteryBoxController extends Controller
{
    public function watchAd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
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

        $settings = Setting::first();
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Box settings not found'
            ], 404);
        }

        $boxType = $request->box_type;
        $cooldownMinutes = (int) $settings->{"{$boxType}_box_cooldown"};
        $adsRequired = (int) $settings->{"{$boxType}_box_ads"};

        // Get or create mystery box claim
        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $boxType)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        $now = Carbon::now();

        if ($claim) {
            // Check cooldown
            if ($claim->cooldown_until && $now < Carbon::parse($claim->cooldown_until)) {
                $secondsRemaining = $now->diffInSeconds(Carbon::parse($claim->cooldown_until));
                return response()->json([
                    'success' => false,
                    'message' => 'Cooldown active. Please wait.',
                    'seconds_remaining' => $secondsRemaining,
                    'cooldown_until' => $claim->cooldown_until
                ], 400);
            }

            // Increment ads watched
            $claim->increment('ads_watched');
            $claim->update([
                'last_ad_watched_at' => $now,
                'cooldown_until' => $cooldownMinutes > 0 ? $now->copy()->addMinutes($cooldownMinutes) : null
            ]);
            $claim->refresh();

            $adsWatched = $claim->ads_watched;
        } else {
            // Create new claim
            $claim = MysteryBoxClaim::create([
                'user_id' => $user->id,
                'box_type' => $boxType,
                'ads_watched' => 1,
                'ads_required' => $adsRequired,
                'last_ad_watched_at' => $now,
                'cooldown_until' => $cooldownMinutes > 0 ? $now->copy()->addMinutes($cooldownMinutes) : null,
                'box_opened' => 0
            ]);
            $adsWatched = 1;
        }

        return response()->json([
            'success' => true,
            'message' => 'Ad watched successfully',
            'ads_watched' => $adsWatched,
            'ads_required' => $adsRequired,
            'can_open' => $adsWatched >= $adsRequired
        ]);
    }

    public function click(Request $request)
    {
        // Track mystery box clicks
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
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

        // Get or create a mystery box claim for tracking
        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $request->box_type)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($claim) {
            $claim->increment('clicks');
            $claim->update(['last_clicked_at' => Carbon::now()]);
        } else {
            // Create a new tracking record if none exists
            $settings = Setting::first();
            $adsRequired = $settings ? (int) $settings->{"{$request->box_type}_box_ads"} : 1;
            
            $claim = MysteryBoxClaim::create([
                'user_id' => $user->id,
                'box_type' => $request->box_type,
                'clicks' => 1,
                'ads_watched' => 0,
                'ads_required' => $adsRequired,
                'last_clicked_at' => Carbon::now(),
                'box_opened' => 0
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'clicks' => $claim->clicks,
            'box_type' => $request->box_type
        ]);
    }

    public function open(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $settings = Setting::first();
        
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found. Please contact administrator.'
            ], 404);
        }
        
        $boxType = $request->box_type;
        
        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);
        
        // Check if user opened this box type within last 24 hours
        $recentlyOpened = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $boxType)
            ->where('box_opened', 1)
            ->where('opened_at', '>=', $twentyFourHoursAgo)
            ->orderBy('opened_at', 'desc')
            ->first();

        if ($recentlyOpened) {
            $nextAvailableAt = Carbon::parse($recentlyOpened->opened_at)->addHours(24);
            $secondsUntilAvailable = $now->diffInSeconds($nextAvailableAt);
            
            return response()->json([
                'success' => false,
                'message' => 'Box already opened. Available again in 24 hours.',
                'next_available_at' => $nextAvailableAt->format('Y-m-d H:i:s'),
                'seconds_until_available' => $secondsUntilAvailable
            ], 400);
        }
        
        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $boxType)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'No active box found'
            ], 404);
        }

        if ($claim->ads_watched < $claim->ads_required) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough ads watched'
            ], 400);
        }

        // Check if legendary box should give booster instead of coins
        $rewardType = $settings->legendary_box_reward_type ?? 'coins';
        
        DB::beginTransaction();

        try {
            $now = Carbon::now();
            
            if ($boxType === 'legendary' && $rewardType === 'booster') {
                // Legendary box gives booster reward
                $boosterTypes = explode(',', $settings->legendary_box_booster_types ?? '2x,3x,5x');
                $boosterTypes = array_map('trim', $boosterTypes);
                $boosterTypes = array_filter($boosterTypes); // Remove empty values
                
                if (empty($boosterTypes)) {
                    $boosterTypes = ['2x', '3x', '5x']; // Default fallback
                }
                
                // Randomly select a booster type
                $selectedBooster = $boosterTypes[array_rand($boosterTypes)];
                
                // Get booster duration (default 10 hours)
                $boosterDurationHours = (float) ($settings->legendary_box_booster_duration ?? 10.00);
                $boosterDurationSeconds = (int) ($boosterDurationHours * 3600);
                $expiresAt = $now->copy()->addSeconds($boosterDurationSeconds);
                
                // Deactivate all existing active boosters for this user
                UserBooster::where('user_id', $user->id)
                    ->where('is_active', 1)
                    ->update(['is_active' => 0]);
                
                // Create new booster
                $booster = UserBooster::create([
                    'user_id' => $user->id,
                    'booster_type' => $selectedBooster,
                    'started_at' => $now,
                    'expires_at' => $expiresAt,
                    'is_active' => 1,
                    'created_at' => $now
                ]);
                
                // Update claim
                $claim->update([
                    'box_opened' => 1,
                    'reward_coins' => 0, // No coins for legendary when booster is enabled
                    'opened_at' => $now
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Box opened successfully',
                    'reward_type' => 'booster',
                    'booster_type' => $selectedBooster,
                    'booster_duration_hours' => $boosterDurationHours,
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'new_balance' => (float) $user->token
                ]);
            } else {
                // Other boxes or legendary with coins enabled - give coins
                $minCoins = (float) ($settings->{"{$boxType}_box_min_coins"} ?? 1.00);
                $maxCoins = (float) ($settings->{"{$boxType}_box_max_coins"} ?? 5.00);
                
                // Generate random reward
                $rewardCoins = round(rand($minCoins * 100, $maxCoins * 100) / 100, 2);
                
                $claim->update([
                    'box_opened' => 1,
                    'reward_coins' => $rewardCoins,
                    'opened_at' => $now
                ]);

                // Add coins to mining balance (token)
                $user->increment('token', $rewardCoins);
                
                // If mining is active, adjust mining_start_balance so balance calculation continues correctly
                if ($user->is_mining == 1 && $user->mining_start_balance !== null) {
                    $user->increment('mining_start_balance', $rewardCoins);
                }
                
                $user->refresh();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Box opened successfully',
                    'reward_type' => 'coins',
                    'reward_coins' => $rewardCoins,
                    'new_balance' => (float) $user->token,
                    'is_mining_active' => $user->is_mining == 1
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error opening box: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetails(Request $request)
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

        $settings = Setting::first();
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found'
            ], 404);
        }

        $boxTypes = ['common', 'rare', 'epic', 'legendary'];
        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);
        $mysteryBoxData = [];

        foreach ($boxTypes as $boxType) {
            // Check if user has opened this box type within the last 24 hours
            $recentlyOpened = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->where('box_opened', 1)
                ->where('opened_at', '>=', $twentyFourHoursAgo)
                ->orderBy('opened_at', 'desc')
                ->first();

            // If box was opened within 24 hours, exclude it from the list
            if ($recentlyOpened) {
                $resetTime = Carbon::parse($recentlyOpened->opened_at)->addHours(24);
                $secondsUntilReset = $now->diffInSeconds($resetTime);
                
                // Don't include this box type in the response
                continue;
            }

            // Auto-reset boxes that were opened more than 24 hours ago
            $oldOpenedBoxes = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->where('box_opened', 1)
                ->where('opened_at', '<', $twentyFourHoursAgo)
                ->get();
            
            // Delete old opened boxes to allow new ones
            foreach ($oldOpenedBoxes as $oldBox) {
                $oldBox->delete();
            }

            // Get active (not opened) claim
            $activeClaim = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->where('box_opened', 0)
                ->orderBy('created_at', 'desc')
                ->first();

            // Get all opened boxes count (excluding recently opened)
            $openedCount = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->where('box_opened', 1)
                ->where('opened_at', '<', $twentyFourHoursAgo)
                ->count();

            // Get total reward from opened boxes
            $totalReward = MysteryBoxClaim::where('user_id', $user->id)
                ->where('box_type', $boxType)
                ->where('box_opened', 1)
                ->sum('reward_coins');

            $cooldownMinutes = (int) $settings->{"{$boxType}_box_cooldown"};
            $adsRequired = (int) $settings->{"{$boxType}_box_ads"};
            $minCoins = (float) ($settings->{"{$boxType}_box_min_coins"} ?? 1.00);
            $maxCoins = (float) ($settings->{"{$boxType}_box_max_coins"} ?? 5.00);

            $boxData = [
                'box_type' => $boxType,
                'settings' => [
                    'cooldown_minutes' => $cooldownMinutes,
                    'ads_required' => $adsRequired,
                    'min_coins' => $minCoins,
                    'max_coins' => $maxCoins,
                ],
                'statistics' => [
                    'total_opened' => $openedCount,
                    'total_reward_earned' => (float) $totalReward,
                ],
            ];

            if ($activeClaim) {
                $secondsRemaining = 0;
                $isOnCooldown = false;
                if ($activeClaim->cooldown_until && $now < Carbon::parse($activeClaim->cooldown_until)) {
                    $secondsRemaining = $now->diffInSeconds(Carbon::parse($activeClaim->cooldown_until));
                    $isOnCooldown = true;
                }

                $boxData['active_box'] = [
                    'clicks' => $activeClaim->clicks ?? 0,
                    'ads_watched' => $activeClaim->ads_watched,
                    'ads_required' => $activeClaim->ads_required,
                    'can_open' => $activeClaim->ads_watched >= $activeClaim->ads_required,
                    'is_on_cooldown' => $isOnCooldown,
                    'seconds_remaining' => $secondsRemaining,
                    'cooldown_until' => $activeClaim->cooldown_until,
                    'last_clicked_at' => $activeClaim->last_clicked_at,
                    'last_ad_watched_at' => $activeClaim->last_ad_watched_at,
                ];
            } else {
                $boxData['active_box'] = null;
            }

            $mysteryBoxData[] = $boxData;
        }

        return response()->json([
            'success' => true,
            'user_email' => $user->email,
            'user_id' => $user->id,
            'mystery_boxes' => $mysteryBoxData
        ]);
    }
}
