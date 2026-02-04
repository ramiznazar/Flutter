<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Currency;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Return app settings for Flutter (other_settings). Ensures the app is never
     * blocked by maintenance/force_update from imported or stale data:
     * - When no row exists, returns safe defaults.
     * - maintenance and force_update are always returned as '0' so the app can run.
     *   (Admin can use Laravel maintenance mode or a separate mechanism to block if needed.)
     */
    public function otherSettings(Request $request)
    {
        $settings = Setting::first();

        if (!$settings) {
            $defaults = array_merge(Setting::defaultAttributes(), ['id' => 1]);
            return response()->json([$defaults], 200);
        }

        $data = $settings->toArray();

        // Always allow the app to run: never send maintenance or force_update that would block.
        // Imported data (e.g. new_data.sql) often has maintenance='1' or force_update='1';
        // overriding here fixes "update related issue" when Postman works but the app does not.
        $data['maintenance'] = '0';
        $data['force_update'] = '0';

        // Ensure other common keys exist so the app does not crash on missing fields
        $data['update_version'] = $data['update_version'] ?? '1.0.0';
        $data['update_message'] = $data['update_message'] ?? '';
        $data['update_link'] = $data['update_link'] ?? '';
        $data['maintenance_message'] = $data['maintenance_message'] ?? '';

        return response()->json([$data], 200);
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
        // Get settings (manual/fake values from settings table) - cache per PHP process
        static $cachedSettings = null;
        if ($cachedSettings === null) {
            $cachedSettings = Setting::select('current_users', 'goal_users')->first();
        }
        $settings = $cachedSettings;
        
        // Cache real user count per PHP process (updates every ~60 seconds to balance freshness vs performance)
        static $cachedRealUsers = null;
        static $cachedRealUsersTime = null;
        $now = time();
        if ($cachedRealUsers === null || ($cachedRealUsersTime !== null && ($now - $cachedRealUsersTime) > 60)) {
            $cachedRealUsers = \App\Models\User::count();
            $cachedRealUsersTime = $now;
        }
        $realUsers = $cachedRealUsers;
        
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
        // Every key as string (never null) so Flutter AdsModel.fromJson never gets "type 'Null' is not a subtype of type 'String'" at line 31
        // Include id and admob_inter_id, meta_inter_id, unity_inter_id per frontend spec (Issue #4)
        $data = [
            'id' => '1',
            'applovin_sdk_key' => '',
            'applovin_inter_id' => '',
            'applovin_reward_id' => '',
            'applovin_native_id' => '',
            'applovin_banner_id' => '',
            'applovin_app_open_id' => '',
            'applovin_mrec_id' => '',
            'admob_app_id' => '',
            'admob_banner_id' => '',
            'admob_interstitial_id' => '',
            'admob_inter_id' => '',
            'admob_rewarded_id' => '',
            'admod_inter_id' => '',
            'meta_inter_id' => '',
            'facebook_inter_id' => '',
            'unity_inter_id' => '',
            'unity_ads_game_id' => '',
            'chartboost_app_id' => '',
            'vungle_app_id' => '',
            'iron_source_app_key' => '',
            'status' => '0',
        ];

        $adsSetting = \App\Models\AdsSetting::select('id', 'applovin_sdk_key', 'applovin_inter_id', 'applovin_reward_id', 'applovin_native_id', 'status')->first();
        if ($adsSetting) {
            $data['id'] = (string) ($adsSetting->id ?? '1');
            $data['applovin_sdk_key'] = (string) ($adsSetting->applovin_sdk_key ?? '');
            $data['applovin_inter_id'] = (string) ($adsSetting->applovin_inter_id ?? '');
            $data['applovin_reward_id'] = (string) ($adsSetting->applovin_reward_id ?? '');
            $data['applovin_native_id'] = (string) ($adsSetting->applovin_native_id ?? '');
            $data['status'] = (string) (($adsSetting->status !== null && $adsSetting->status !== '') ? (int) $adsSetting->status : 0);
        }

        // Ensure no key is ever null (Flutter casts to String)
        $data = array_map(function ($v) {
            return $v === null ? '' : (is_int($v) ? (string) $v : $v);
        }, $data);

        // Return data wrapped; also merge data keys at root so if Flutter uses fromJson(fullResponse) it still gets all keys (no null)
        return response()->json(array_merge([
            'success' => true,
            'data' => $data
        ], $data), 200);
    }
}
