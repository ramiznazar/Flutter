<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Currency;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function otherSettings(Request $request)
    {
        $settings = Setting::first();

        if (!$settings) {
            return response()->json([], 200);
        }

        return response()->json([$settings->toArray()]);
    }

    public function getCurrencies(Request $request)
    {
        $currencies = Currency::where('status', 1)
            ->select('currency', 'value', 'icon')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $currencies
        ]);
    }

    public function getTotalUsers(Request $request)
    {
        $settings = Setting::first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'current_users' => $settings ? (int) $settings->current_users : 0,
                'goal_users' => $settings ? (int) $settings->goal_users : 0
            ]
        ]);
    }

    public function time(Request $request)
    {
        return response()->json([
            'success' => true,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'timestamp' => now()->timestamp
        ]);
    }

    public function ads(Request $request)
    {
        $adsSetting = \App\Models\AdsSetting::first();

        if (!$adsSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Ads settings not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'applovin_sdk_key' => $adsSetting->applovin_sdk_key,
                'applovin_inter_id' => $adsSetting->applovin_inter_id,
                'applovin_reward_id' => $adsSetting->applovin_reward_id,
                'applovin_native_id' => $adsSetting->applovin_native_id,
                'status' => $adsSetting->status
            ]
        ]);
    }
}
