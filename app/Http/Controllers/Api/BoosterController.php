<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BoosterController extends Controller
{
    public function boosterStatus(Request $request)
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

        // Get active booster
        $booster = UserBooster::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($booster) {
            $now = Carbon::now();
            $expiresAt = Carbon::parse($booster->expires_at);
            $secondsRemaining = $now->diffInSeconds($expiresAt);

            return response()->json([
                'success' => true,
                'has_active_booster' => true,
                'booster_type' => $booster->booster_type,
                'started_at' => $booster->started_at->format('Y-m-d H:i:s'),
                'expires_at' => $booster->expires_at->format('Y-m-d H:i:s'),
                'seconds_remaining' => $secondsRemaining
            ]);
        }

        // Deactivate any expired boosters
        UserBooster::where('user_id', $user->id)
            ->where('expires_at', '<=', Carbon::now())
            ->update(['is_active' => 0]);

        return response()->json([
            'success' => true,
            'has_active_booster' => false
        ]);
    }

    public function boosterClaim(Request $request)
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

        // Check for active booster
        $activeBooster = UserBooster::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($activeBooster) {
            $expiresAt = Carbon::parse($activeBooster->expires_at);
            $now = Carbon::now();
            $secondsRemaining = $now->diffInSeconds($expiresAt);

            return response()->json([
                'success' => false,
                'message' => 'Booster already active',
                'expires_at' => $activeBooster->expires_at->format('Y-m-d H:i:s'),
                'seconds_remaining' => $secondsRemaining
            ], 400);
        }

        // Deactivate any expired boosters
        UserBooster::where('user_id', $user->id)
            ->where('expires_at', '<=', Carbon::now())
            ->update(['is_active' => 0]);

        // Create new booster (2x, 1 hour duration)
        $now = Carbon::now();
        $expiresAt = $now->copy()->addHour();

        $booster = UserBooster::create([
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
            'booster_type' => '2x',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'duration_seconds' => 3600
        ]);
    }
}
