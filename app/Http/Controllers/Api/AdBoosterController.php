<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Models\UserBooster;
use App\Models\AdBoosterClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdBoosterController extends Controller
{
    /**
     * Get ad booster status for the user: can they claim, cooldown remaining, claims today, etc.
     * POST /api/ad_booster_status — no cache (avoids hang on Play Store).
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
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
            Log::error('ad_booster_status 500', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email'),
            ]);
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

    /**
     * Claim ad booster after user watched the ad. Applies booster and records claim.
     * POST /api/ad_booster_claim — no cache.
     */
    public function claim(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
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
            $startedAtFormatted = $now->format('Y-m-d H:i:s');
            $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');
            $secondsRemaining = $durationSeconds;

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
                'started_at' => $startedAtFormatted,
                'expires_at' => $expiresAtFormatted,
                'seconds_remaining' => $secondsRemaining,
                'claims_today' => $claimsToday + 1,
                'max_per_day' => $maxPerDay,
            ]);
        } catch (\Throwable $e) {
            Log::error('ad_booster_claim 500', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email'),
            ]);
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
}
