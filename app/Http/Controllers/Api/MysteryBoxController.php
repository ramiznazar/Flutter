<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MysteryBoxClaim;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $boxType = $request->box_type;
        $minCoins = (float) $settings->{"{$boxType}_box_min_coins"};
        $maxCoins = (float) $settings->{"{$boxType}_box_max_coins"};

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

        // Generate random reward
        $rewardCoins = round(rand($minCoins * 100, $maxCoins * 100) / 100, 2);

        // Update claim and give reward
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $claim->update([
                'box_opened' => 1,
                'reward_coins' => $rewardCoins,
                'opened_at' => Carbon::now()
            ]);

            $user->increment('token', $rewardCoins);
            $user->refresh();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Box opened successfully',
                'reward_coins' => $rewardCoins,
                'new_balance' => (float) $user->token
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error opening box: ' . $e->getMessage()
            ], 500);
        }
    }
}
