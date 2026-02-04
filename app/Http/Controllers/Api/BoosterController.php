<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BoosterController extends Controller
{
    /**
     * No cache used — avoids file lock / hang when user has active booster (Play Store).
     */
    public function boosterStatus(Request $request)
    {
        try {
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
                ->select('id')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or account not active'
                ], 404);
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
                $expiresAt = $booster->expires_at instanceof \Carbon\Carbon
                    ? $booster->expires_at
                    : Carbon::parse($booster->expires_at);
                $secondsRemaining = (int) $now->diffInSeconds($expiresAt, false);
                if ($secondsRemaining < 0) {
                    $secondsRemaining = 0;
                }
                $startedAtFormatted = $booster->started_at !== null
                    ? ($booster->started_at instanceof \Carbon\Carbon ? $booster->started_at : Carbon::parse($booster->started_at))->format('Y-m-d H:i:s')
                    : $now->format('Y-m-d H:i:s');
                $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');
                $boosterType = trim((string) ($booster->booster_type ?? '2x'));
                if ($boosterType === '') {
                    $boosterType = '2x';
                }
                return response()->json([
                    'success' => true,
                    'has_active_booster' => true,
                    'booster_type' => $boosterType,
                    'started_at' => $startedAtFormatted,
                    'expires_at' => $expiresAtFormatted,
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
            Log::error('booster_status 500', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email'),
            ]);
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

    public function boosterClaim(Request $request)
    {
        try {
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
                ->select('id')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or account not active'
                ], 404);
            }

            $activeBooster = UserBooster::where('user_id', $user->id)
                ->where('is_active', 1)
                ->where('expires_at', '>', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($activeBooster && $activeBooster->expires_at !== null) {
                $expiresAt = $activeBooster->expires_at instanceof \Carbon\Carbon
                    ? $activeBooster->expires_at
                    : Carbon::parse($activeBooster->expires_at);
                $now = Carbon::now();
                $secondsRemaining = (int) $now->diffInSeconds($expiresAt, false);
                if ($secondsRemaining < 0) {
                    $secondsRemaining = 0;
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Booster already active',
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'seconds_remaining' => $secondsRemaining
                ], 400);
            }

            UserBooster::where('user_id', $user->id)
                ->where('expires_at', '<=', Carbon::now())
                ->update(['is_active' => 0]);

            $now = Carbon::now();
            $expiresAt = $now->copy()->addHour();

            UserBooster::create([
                'user_id' => $user->id,
                'booster_type' => '2x',
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'is_active' => 1,
                'created_at' => $now
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
            Log::error('booster_claim 500', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email'),
            ]);
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
}
