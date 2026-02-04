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
            ->select('id')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or account not active'
            ], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Box settings not found'
            ], 404);
        }

        $boxType = $request->box_type;
        if ((int) ($settings->{"{$boxType}_box_enabled"} ?? 1) !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This box type is not available'
            ], 400);
        }

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

        $user = User::where('email', $request->email)->select('id')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if ($settings && (int) ($settings->{"{$request->box_type}_box_enabled"} ?? 1) !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This box type is not available'
            ], 400);
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
            ->select('id', 'token', 'is_mining', 'mining_start_balance')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found. Please contact administrator.'
            ], 404);
        }
        
        $boxType = $request->box_type;
        if ((int) ($settings->{"{$boxType}_box_enabled"} ?? 1) !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This box type is not available'
            ], 400);
        }
        
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

        // Reward type per box (common, rare, epic, legendary): booster or coins
        $rewardType = $settings->{"{$boxType}_box_reward_type"} ?? 'coins';
        
        DB::beginTransaction();

        try {
            $now = Carbon::now();
            
            if ($rewardType === 'booster') {
                // Any box type can give booster when reward_type is booster
                $boosterTypesStr = $settings->{"{$boxType}_box_booster_types"} ?? '2x,3x,5x';
                $boosterTypes = array_map('trim', explode(',', $boosterTypesStr));
                $boosterTypes = array_filter($boosterTypes);
                if (empty($boosterTypes)) {
                    $boosterTypes = ['2x', '3x', '5x'];
                }
                $selectedBooster = $boosterTypes[array_rand($boosterTypes)];
                $boosterDurationHours = (float) ($settings->{"{$boxType}_box_booster_duration"} ?? 10.00);
                $boosterDurationSeconds = (int) ($boosterDurationHours * 3600);
                $expiresAt = $now->copy()->addSeconds($boosterDurationSeconds);
                
                UserBooster::where('user_id', $user->id)->where('is_active', 1)->update(['is_active' => 0]);
                UserBooster::create([
                    'user_id' => $user->id,
                    'booster_type' => $selectedBooster,
                    'started_at' => $now,
                    'expires_at' => $expiresAt,
                    'is_active' => 1,
                    'created_at' => $now
                ]);
                $claim->update(['box_opened' => 1, 'reward_coins' => 0, 'opened_at' => $now]);
                
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
            }
            
            // Coins reward
            {
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

        $user = User::where('email', $request->email)->select('id', 'email')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found'
            ], 404);
        }

        // Only include box types that are enabled (shown in app)
        $allBoxTypes = ['common', 'rare', 'epic', 'legendary'];
        $boxTypes = array_values(array_filter($allBoxTypes, function ($type) use ($settings) {
            return (int) ($settings->{"{$type}_box_enabled"} ?? 1) === 1;
        }));
        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

        // Load all mystery box claims for this user in one query to avoid N+1
        $allClaims = MysteryBoxClaim::where('user_id', $user->id)
            ->whereIn('box_type', $allBoxTypes)
            ->select('id', 'box_type', 'box_opened', 'opened_at', 'created_at', 'ads_watched', 'ads_required', 'cooldown_until', 'last_clicked_at', 'last_ad_watched_at', 'clicks', 'reward_coins')
            ->orderBy('opened_at', 'desc')
            ->get()
            ->groupBy('box_type');

        $mysteryBoxData = [];

        foreach ($boxTypes as $boxType) {
            $claimsForType = $allClaims->get($boxType, collect());
            $recentlyOpened = $claimsForType->where('box_opened', 1)->where('opened_at', '>=', $twentyFourHoursAgo)->first();

            if ($recentlyOpened) {
                continue;
            }

            // Auto-reset: delete old opened boxes (opened > 24h ago)
            $oldOpenedIds = $claimsForType->where('box_opened', 1)->where('opened_at', '<', $twentyFourHoursAgo)->pluck('id');
            if ($oldOpenedIds->isNotEmpty()) {
                MysteryBoxClaim::whereIn('id', $oldOpenedIds)->delete();
            }

            $activeClaim = $claimsForType->where('box_opened', 0)->sortByDesc('created_at')->first();
            $openedCount = $claimsForType->where('box_opened', 1)->where('opened_at', '<', $twentyFourHoursAgo)->count();
            $totalReward = $claimsForType->where('box_opened', 1)->sum('reward_coins');

            $cooldownMinutes = (int) $settings->{"{$boxType}_box_cooldown"};
            $adsRequired = (int) $settings->{"{$boxType}_box_ads"};
            $minCoins = (float) ($settings->{"{$boxType}_box_min_coins"} ?? 1.00);
            $maxCoins = (float) ($settings->{"{$boxType}_box_max_coins"} ?? 5.00);
            $rewardType = $settings->{"{$boxType}_box_reward_type"} ?? 'booster';
            $boosterTypes = $settings->{"{$boxType}_box_booster_types"} ?? '2x,3x,5x';
            $boosterDuration = (float) ($settings->{"{$boxType}_box_booster_duration"} ?? 10.00);

            $boxData = [
                'box_type' => $boxType,
                'settings' => [
                    'cooldown_minutes' => $cooldownMinutes,
                    'ads_required' => $adsRequired,
                    'min_coins' => $minCoins,
                    'max_coins' => $maxCoins,
                    'reward_type' => $rewardType,
                    'booster_types' => $boosterTypes,
                    'booster_duration' => $boosterDuration,
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

    /**
     * Columns from settings used for mystery box (reduces data transfer).
     */
    private function mysteryBoxSettingColumns(): array
    {
        $cols = [];
        foreach (['common', 'rare', 'epic', 'legendary'] as $t) {
            $cols[] = "{$t}_box_cooldown";
            $cols[] = "{$t}_box_ads";
            $cols[] = "{$t}_box_min_coins";
            $cols[] = "{$t}_box_max_coins";
            $cols[] = "{$t}_box_enabled";
            $cols[] = "{$t}_box_reward_type";
            $cols[] = "{$t}_box_booster_types";
            $cols[] = "{$t}_box_booster_duration";
        }
        return $cols;
    }
}
