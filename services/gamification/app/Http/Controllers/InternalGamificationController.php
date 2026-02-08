<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MysteryBoxClaim;
use App\Models\Setting;
use App\Models\UserBooster;
use App\Models\AdBoosterClaim;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Internal gamification API (Mystery Box, Booster, Ad Booster). Request/response match monolith.
 */
class InternalGamificationController extends Controller
{
    public function mysteryBoxWatchAd(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->select('id')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found or account not active'], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if (!$settings) {
            return response()->json(['success' => false, 'message' => 'Box settings not found'], 404);
        }

        $boxType = $request->box_type;
        if ((int) ($settings->{"{$boxType}_box_enabled"} ?? 1) !== 1) {
            return response()->json(['success' => false, 'message' => 'This box type is not available'], 400);
        }

        $cooldownMinutes = (int) $settings->{"{$boxType}_box_cooldown"};
        $adsRequired = (int) $settings->{"{$boxType}_box_ads"};

        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $boxType)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        $now = Carbon::now();

        if ($claim) {
            if ($claim->cooldown_until && $now < Carbon::parse($claim->cooldown_until)) {
                $secondsRemaining = $now->diffInSeconds(Carbon::parse($claim->cooldown_until));
                return response()->json([
                    'success' => false,
                    'message' => 'Cooldown active. Please wait.',
                    'seconds_remaining' => $secondsRemaining,
                    'cooldown_until' => $claim->cooldown_until,
                ], 400);
            }

            $claim->increment('ads_watched');
            $claim->update([
                'last_ad_watched_at' => $now,
                'cooldown_until' => $cooldownMinutes > 0 ? $now->copy()->addMinutes($cooldownMinutes) : null,
            ]);
            $claim->refresh();
            $adsWatched = $claim->ads_watched;
        } else {
            $claim = MysteryBoxClaim::create([
                'user_id' => $user->id,
                'box_type' => $boxType,
                'ads_watched' => 1,
                'ads_required' => $adsRequired,
                'last_ad_watched_at' => $now,
                'cooldown_until' => $cooldownMinutes > 0 ? $now->copy()->addMinutes($cooldownMinutes) : null,
                'box_opened' => 0,
            ]);
            $adsWatched = 1;
        }

        return response()->json([
            'success' => true,
            'message' => 'Ad watched successfully',
            'ads_watched' => $adsWatched,
            'ads_required' => $adsRequired,
            'can_open' => $adsWatched >= $adsRequired,
        ]);
    }

    public function mysteryBoxClick(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $user = User::where('email', $request->email)->select('id')->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if ($settings && (int) ($settings->{"{$request->box_type}_box_enabled"} ?? 1) !== 1) {
            return response()->json(['success' => false, 'message' => 'This box type is not available'], 400);
        }

        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $request->box_type)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($claim) {
            $claim->increment('clicks');
            $claim->update(['last_clicked_at' => Carbon::now()]);
        } else {
            $adsRequired = $settings ? (int) $settings->{"{$request->box_type}_box_ads"} : 1;
            $claim = MysteryBoxClaim::create([
                'user_id' => $user->id,
                'box_type' => $request->box_type,
                'clicks' => 1,
                'ads_watched' => 0,
                'ads_required' => $adsRequired,
                'last_clicked_at' => Carbon::now(),
                'box_opened' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'clicks' => $claim->clicks,
            'box_type' => $request->box_type,
        ]);
    }

    public function mysteryBoxOpen(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'box_type' => 'required|in:common,rare,epic,legendary',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $user = User::where('email', $request->email)
            ->where('account_status', 'active')
            ->select('id', 'token', 'is_mining', 'mining_start_balance')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if (!$settings) {
            return response()->json(['success' => false, 'message' => 'Settings not found. Please contact administrator.'], 404);
        }

        $boxType = $request->box_type;
        if ((int) ($settings->{"{$boxType}_box_enabled"} ?? 1) !== 1) {
            return response()->json(['success' => false, 'message' => 'This box type is not available'], 400);
        }

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

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
                'seconds_until_available' => $secondsUntilAvailable,
            ], 400);
        }

        $claim = MysteryBoxClaim::where('user_id', $user->id)
            ->where('box_type', $boxType)
            ->where('box_opened', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'No active box found'], 404);
        }

        if ($claim->ads_watched < $claim->ads_required) {
            return response()->json(['success' => false, 'message' => 'Not enough ads watched'], 400);
        }

        $rewardType = $settings->{"{$boxType}_box_reward_type"} ?? 'coins';

        DB::beginTransaction();
        try {
            $now = Carbon::now();

            if ($rewardType === 'booster') {
                $boosterTypesStr = $settings->{"{$boxType}_box_booster_types"} ?? '2x,3x,5x';
                $boosterTypes = array_filter(array_map('trim', explode(',', $boosterTypesStr)));
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
                    'created_at' => $now,
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
                    'new_balance' => (float) $user->token,
                ]);
            }

            $minCoins = (float) ($settings->{"{$boxType}_box_min_coins"} ?? 1.00);
            $maxCoins = (float) ($settings->{"{$boxType}_box_max_coins"} ?? 5.00);
            $rewardCoins = round(rand((int) ($minCoins * 100), (int) ($maxCoins * 100)) / 100, 2);

            $claim->update([
                'box_opened' => 1,
                'reward_coins' => $rewardCoins,
                'opened_at' => $now,
            ]);

            $user->increment('token', $rewardCoins);
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
                'is_mining_active' => $user->is_mining == 1,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error opening box: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mysteryBoxDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Email is required'], 400);
        }

        $user = User::where('email', $request->email)->select('id', 'email')->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $settings = Setting::select($this->mysteryBoxSettingColumns())->first();
        if (!$settings) {
            return response()->json(['success' => false, 'message' => 'Settings not found'], 404);
        }

        $allBoxTypes = ['common', 'rare', 'epic', 'legendary'];
        $boxTypes = array_values(array_filter($allBoxTypes, function ($type) use ($settings) {
            return (int) ($settings->{"{$type}_box_enabled"} ?? 1) === 1;
        }));

        $now = Carbon::now();
        $twentyFourHoursAgo = $now->copy()->subHours(24);

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
            'mystery_boxes' => $mysteryBoxData,
        ]);
    }

    public function boosterStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Email is required'], 400);
            }

            $user = User::where('email', $request->email)
                ->where('account_status', 'active')
                ->select('id')
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found or account not active'], 404);
            }

            $booster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->select('booster_type', 'started_at', 'expires_at')
                ->first();

            if ($booster && $booster->expires_at !== null) {
                $now = Carbon::now();
                $expiresAt = $booster->expires_at instanceof Carbon ? $booster->expires_at : Carbon::parse($booster->expires_at);
                $secondsRemaining = (int) $now->diffInSeconds($expiresAt, false);
                if ($secondsRemaining < 0) {
                    $secondsRemaining = 0;
                }
                $startedAtFormatted = $booster->started_at !== null
                    ? ($booster->started_at instanceof Carbon ? $booster->started_at : Carbon::parse($booster->started_at))->format('Y-m-d H:i:s')
                    : $now->format('Y-m-d H:i:s');
                $boosterType = trim((string) ($booster->booster_type ?? '2x')) ?: '2x';
                return response()->json([
                    'success' => true,
                    'has_active_booster' => true,
                    'booster_type' => $boosterType,
                    'started_at' => $startedAtFormatted,
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'seconds_remaining' => $secondsRemaining,
                ]);
            }

            UserBooster::where('user_id', $user->id)
                ->where('expires_at', '<=', Carbon::now())
                ->update(['is_active' => 0]);

            return response()->json([
                'success' => true,
                'has_active_booster' => false,
                'booster_type' => '',
                'started_at' => '',
                'expires_at' => '',
                'seconds_remaining' => 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('booster_status 500', ['message' => $e->getMessage(), 'email' => $request->input('email')]);
            return response()->json([
                'success' => true,
                'has_active_booster' => false,
                'booster_type' => '',
                'started_at' => '',
                'expires_at' => '',
                'seconds_remaining' => 0,
            ], 200);
        }
    }

    public function boosterClaim(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Email is required'], 400);
            }

            $user = User::where('email', $request->email)
                ->where('account_status', 'active')
                ->select('id')
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found or account not active'], 404);
            }

            $activeBooster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('expires_at', '>', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($activeBooster && $activeBooster->expires_at !== null) {
                $expiresAt = $activeBooster->expires_at instanceof Carbon ? $activeBooster->expires_at : Carbon::parse($activeBooster->expires_at);
                $secondsRemaining = (int) Carbon::now()->diffInSeconds($expiresAt, false);
                if ($secondsRemaining < 0) {
                    $secondsRemaining = 0;
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Booster already active',
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'seconds_remaining' => $secondsRemaining,
                ], 400);
            }

            UserBooster::where('user_id', $user->id)->where('expires_at', '<=', Carbon::now())->update(['is_active' => 0]);

            $now = Carbon::now();
            $expiresAt = $now->copy()->addHour();

            UserBooster::create([
                'user_id' => $user->id,
                'booster_type' => '2x',
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'is_active' => 1,
                'created_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booster activated successfully',
                'has_active_booster' => true,
                'booster_type' => '2x',
                'started_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'seconds_remaining' => 3600,
                'duration_seconds' => 3600,
            ]);
        } catch (\Throwable $e) {
            Log::error('booster_claim 500', ['message' => $e->getMessage(), 'email' => $request->input('email')]);
            return response()->json([
                'success' => false,
                'message' => 'Temporary error. Please try again.',
                'has_active_booster' => false,
                'booster_type' => '',
                'started_at' => '',
                'expires_at' => '',
                'seconds_remaining' => 0,
            ], 503);
        }
    }

    public function adBoosterStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Email is required'], 400);
            }

            $user = User::where('email', $request->email)->where('account_status', 'active')->select('id')->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $settings = Setting::select('ad_booster_enabled', 'ad_booster_cooldown_hours', 'ad_booster_duration_hours', 'ad_booster_type', 'ad_booster_max_per_day')->first();
            if (!$settings) {
                return response()->json(['success' => false, 'message' => 'Settings not found'], 404);
            }

            $enabled = (int) ($settings->ad_booster_enabled ?? 0) === 1;
            $cooldownHours = (float) ($settings->ad_booster_cooldown_hours ?? 8);
            $durationHours = (float) ($settings->ad_booster_duration_hours ?? 1);
            $boosterType = $settings->ad_booster_type ?? '3x';
            $maxPerDay = (int) ($settings->ad_booster_max_per_day ?? 3);

            if (!$enabled) {
                return response()->json([
                    'success' => true,
                    'enabled' => false,
                    'message' => 'Ad booster is not available',
                    'can_claim' => false,
                    'has_active_booster' => false,
                    'started_at' => '',
                    'expires_at' => '',
                    'seconds_remaining' => 0,
                ]);
            }

            $now = Carbon::now();
            $todayStart = $now->copy()->startOfDay();

            $claimsToday = AdBoosterClaim::where('user_id', $user->id)
                ->where('claimed_at', '>=', $todayStart)
                ->count();

            $lastClaim = AdBoosterClaim::where('user_id', $user->id)
                ->orderBy('claimed_at', 'desc')
                ->select('claimed_at')
                ->first();

            $canClaim = true;
            $cooldownUntil = '';
            $secondsRemaining = 0;

            if ($claimsToday >= $maxPerDay) {
                $canClaim = false;
            } elseif ($lastClaim && $lastClaim->claimed_at !== null) {
                $lastClaimedAt = $lastClaim->claimed_at instanceof Carbon ? $lastClaim->claimed_at : Carbon::parse($lastClaim->claimed_at);
                $cooldownEnd = $lastClaimedAt->copy()->addHours($cooldownHours);
                if ($now->lt($cooldownEnd)) {
                    $canClaim = false;
                    $cooldownUntil = $cooldownEnd->format('Y-m-d H:i:s');
                    $secondsRemaining = (int) $now->diffInSeconds($cooldownEnd, false);
                    if ($secondsRemaining < 0) {
                        $secondsRemaining = 0;
                    }
                }
            }

            $boosterType = is_string($boosterType) ? trim($boosterType) : '3x';
            if ($boosterType === '') {
                $boosterType = '3x';
            }

            $payload = [
                'success' => true,
                'enabled' => true,
                'can_claim' => $canClaim,
                'cooldown_until' => $cooldownUntil,
                'seconds_remaining' => $secondsRemaining,
                'claims_today' => $claimsToday,
                'max_per_day' => $maxPerDay,
                'cooldown_hours' => $cooldownHours,
                'booster_type' => $boosterType,
                'booster_duration_hours' => $durationHours,
            ];

            $activeBooster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', $now)
                ->orderBy('created_at', 'desc')
                ->select('booster_type', 'started_at', 'expires_at')
                ->first();

            if ($activeBooster && $activeBooster->expires_at !== null) {
                $expiresAt = $activeBooster->expires_at instanceof Carbon ? $activeBooster->expires_at : Carbon::parse($activeBooster->expires_at);
                $secs = (int) $now->diffInSeconds($expiresAt, false);
                if ($secs < 0) {
                    $secs = 0;
                }
                $startedAt = $activeBooster->started_at !== null
                    ? ($activeBooster->started_at instanceof Carbon ? $activeBooster->started_at : Carbon::parse($activeBooster->started_at))->format('Y-m-d H:i:s')
                    : $now->format('Y-m-d H:i:s');
                $payload['has_active_booster'] = true;
                $payload['started_at'] = $startedAt;
                $payload['expires_at'] = $expiresAt->format('Y-m-d H:i:s');
                $payload['seconds_remaining'] = $secs;
            } else {
                $payload['has_active_booster'] = false;
                $payload['started_at'] = '';
                $payload['expires_at'] = '';
            }

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('ad_booster_status 500', ['message' => $e->getMessage(), 'email' => $request->input('email')]);
            return response()->json([
                'success' => true,
                'enabled' => false,
                'can_claim' => false,
                'message' => 'Ad booster is not available',
                'has_active_booster' => false,
                'started_at' => '',
                'expires_at' => '',
                'seconds_remaining' => 0,
            ], 200);
        }
    }

    public function adBoosterClaim(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email']);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Email is required'], 400);
            }

            $user = User::where('email', $request->email)->where('account_status', 'active')->select('id')->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $settings = Setting::select('ad_booster_enabled', 'ad_booster_cooldown_hours', 'ad_booster_duration_hours', 'ad_booster_type', 'ad_booster_max_per_day')->first();
            if (!$settings) {
                return response()->json(['success' => false, 'message' => 'Settings not found'], 404);
            }

            $enabled = (int) ($settings->ad_booster_enabled ?? 0) === 1;
            if (!$enabled) {
                return response()->json(['success' => false, 'message' => 'Ad booster is not available'], 400);
            }

            $cooldownHours = (float) ($settings->ad_booster_cooldown_hours ?? 8);
            $durationHours = (float) ($settings->ad_booster_duration_hours ?? 1);
            $boosterType = trim($settings->ad_booster_type ?? '3x') ?: '3x';
            $maxPerDay = (int) ($settings->ad_booster_max_per_day ?? 3);

            $now = Carbon::now();
            $todayStart = $now->copy()->startOfDay();

            $claimsToday = AdBoosterClaim::where('user_id', $user->id)
                ->where('claimed_at', '>=', $todayStart)
                ->count();

            if ($claimsToday >= $maxPerDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum ad boosters reached for today',
                    'claims_today' => $claimsToday,
                    'max_per_day' => $maxPerDay,
                ], 400);
            }

            $lastClaim = AdBoosterClaim::where('user_id', $user->id)->orderBy('claimed_at', 'desc')->select('claimed_at')->first();
            if ($lastClaim && $lastClaim->claimed_at !== null) {
                $lastClaimedAt = $lastClaim->claimed_at instanceof Carbon ? $lastClaim->claimed_at : Carbon::parse($lastClaim->claimed_at);
                $cooldownEnd = $lastClaimedAt->copy()->addHours($cooldownHours);
                if ($now->lt($cooldownEnd)) {
                    $secondsRemaining = (int) $now->diffInSeconds($cooldownEnd, false);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cooldown active. Please wait before claiming again.',
                        'cooldown_until' => $cooldownEnd->format('Y-m-d H:i:s'),
                        'seconds_remaining' => max(0, $secondsRemaining),
                    ], 400);
                }
            }

            $durationSeconds = (int) round($durationHours * 3600);
            $expiresAt = $now->copy()->addSeconds($durationSeconds);

            UserBooster::where('user_id', $user->id)->where('is_active', 1)->update(['is_active' => 0]);
            UserBooster::create([
                'user_id' => $user->id,
                'booster_type' => $boosterType,
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'is_active' => 1,
                'created_at' => $now,
            ]);

            AdBoosterClaim::create([
                'user_id' => $user->id,
                'claimed_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booster applied successfully',
                'has_active_booster' => true,
                'booster_type' => $boosterType,
                'booster_duration_hours' => (float) $durationHours,
                'started_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'seconds_remaining' => $durationSeconds,
                'claims_today' => $claimsToday + 1,
                'max_per_day' => $maxPerDay,
            ]);
        } catch (\Throwable $e) {
            Log::error('ad_booster_claim 500', ['message' => $e->getMessage(), 'email' => $request->input('email')]);
            return response()->json([
                'success' => false,
                'message' => 'Temporary error. Please try again.',
                'has_active_booster' => false,
                'started_at' => '',
                'expires_at' => '',
                'seconds_remaining' => 0,
            ], 503);
        }
    }

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
