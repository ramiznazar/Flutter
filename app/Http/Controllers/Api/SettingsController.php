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
        // Get actual user count from users table (real users)
        $realUsers = \App\Models\User::count();
        
        // Get settings (manual/fake values from settings table)
        $settings = Setting::first();
        
        $displayUsers = $settings && $settings->current_users !== null 
            ? (int) $settings->current_users 
            : $realUsers; // Default to real users if not set
        
        $goalUsers = $settings && $settings->goal_users !== null 
            ? (int) $settings->goal_users 
            : 1000000; // Default goal
        
        // Calculate progress percentage
        $progressPercent = $goalUsers > 0 
            ? ($displayUsers / $goalUsers * 100) 
            : 0;
        if ($progressPercent > 100) $progressPercent = 100;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $displayUsers,  // Display users (fake/manual) - shown in app
                'current_users' => $displayUsers, // Alias for compatibility
                'real_users' => $realUsers,       // Real registered users from database
                'goal_users' => $goalUsers,       // Goal users
                'progress_percent' => round($progressPercent, 1) // Progress percentage
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
