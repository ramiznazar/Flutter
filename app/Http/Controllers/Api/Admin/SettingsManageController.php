<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SettingsManageController extends Controller
{
    /**
     * GET /api/admin/settings_manage — App config for Crutox mobile app.
     * Response must be exactly: { "success": true, "data": { ... } }, HTTP 200.
     * App uses AppSettings.fromJson(data). Sends maintenance=0, force_update=0, and update_version
     * equal to the current app version (config app.mobile_app_version, default 1.1.9) so the app
     * does not show "Update available". Empty string is formatted to ".0.0" by the app and triggers the sheet.
     * Privacy link key is pirvacy_policy_link (app typo). Use ?format=array for [{ ... }].
     */
    public function index(Request $request)
    {
        try {
            $data = $this->buildAppSettingsData();
        } catch (\Throwable $e) {
            $data = $this->minimalNonBlockingData();
        }

        if ($request->query('format') === 'array') {
            return response()->json([$data], 200)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }
        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Minimal payload that never blocks the app: no maintenance, no update prompt.
     * Used when buildAppSettingsData throws (e.g. DB error) so the app still gets 200 and can run.
     * update_version must match app (e.g. "1.1.9"); empty string is formatted to ".0.0" by the app and triggers the update sheet.
     */
    private function minimalNonBlockingData(): array
    {
        return [
            'maintenance' => 0,
            'maintenance_message' => '',
            'force_update' => 0,
            'update_version' => (string) config('app.mobile_app_version', '1.1.9'),
            'update_message' => '',
            'update_link' => '',
        ];
    }

    /**
     * Build the exact "data" object the Flutter app expects (AppSettings.fromJson).
     * Keys snake_case. maintenance/force_update always 0. Types: int/string as app expects.
     */
    private function buildAppSettingsData(): array
    {
        // Read settings fresh so admin panel changes (e.g. maintenance mode) take effect on next app request
        $settings = Setting::first();
        $defaults = array_merge(Setting::defaultAttributes(), [
            'mining_speed' => 10, 'base_mining_rate' => 5, 'max_mining_speed' => 50,
            'referrer_reward' => 50, 'referee_reward' => 25, 'max_referrals' => 100, 'bonus_reward' => 500,
            'current_users' => 99000, 'goal_users' => 1000000,
        ]);
        $raw = $settings ? $settings->toArray() : $defaults;

        $data = [];

        // Maintenance / update — read from DB so admin panel (App Settings) controls them.
        // When maintenance=1 the app can show maintenance screen; when force_update=1 and update_version
        // is set, the app can show update prompt. If update_version is empty we fall back to config
        // so the app does not get an invalid version string.
        $data['maintenance'] = (int) ($raw['maintenance'] ?? 0);
        $data['maintenance_message'] = (string) ($raw['maintenance_message'] ?? '');
        $data['force_update'] = (int) ($raw['force_update'] ?? 0);
        $data['update_version'] = (string) ($raw['update_version'] ?? '') !== ''
            ? (string) $raw['update_version']
            : (string) config('app.mobile_app_version', '1.1.9');
        $data['update_message'] = (string) ($raw['update_message'] ?? '');
        $data['update_link'] = (string) ($raw['update_link'] ?? '');

        // id and links — app expects pirvacy_policy_link (typo). Support both DB column spellings.
        $data['id'] = (int) ($raw['id'] ?? 1);
        $data['pirvacy_policy_link'] = (string) ($raw['pirvacy_policy_link'] ?? $raw['privacy_policy_link'] ?? '');
        $data['term_n_condition_link'] = (string) ($raw['term_n_condition_link'] ?? '');
        $data['support_email'] = (string) ($raw['support_email'] ?? '');
        $data['faq_link'] = (string) ($raw['faq_link'] ?? '');
        $data['white_paper_link'] = (string) ($raw['white_paper_link'] ?? '');
        $data['road_map_link'] = (string) ($raw['road_map_link'] ?? '');
        $data['about_us_link'] = (string) ($raw['about_us_link'] ?? '');

        // Mining
        $data['mining_speed'] = isset($raw['mining_speed']) ? (float) $raw['mining_speed'] : 10.0;
        $data['base_mining_rate'] = isset($raw['base_mining_rate']) ? (float) $raw['base_mining_rate'] : 5.0;
        $data['max_mining_speed'] = isset($raw['max_mining_speed']) ? (float) $raw['max_mining_speed'] : 50.0;

        // Referral
        $data['referrer_reward'] = (int) ($raw['referrer_reward'] ?? 50);
        $data['referee_reward'] = (int) ($raw['referee_reward'] ?? 25);
        $data['max_referrals'] = (int) ($raw['max_referrals'] ?? 100);
        $data['bonus_reward'] = (int) ($raw['bonus_reward'] ?? 500);

        // User count
        $data['current_users'] = (int) ($raw['current_users'] ?? 99000);
        $data['goal_users'] = (int) ($raw['goal_users'] ?? 1000000);
        $data['daily_tasks_reset_time'] = isset($raw['daily_tasks_reset_time']) && $raw['daily_tasks_reset_time'] !== null
            ? (string) $raw['daily_tasks_reset_time'] : '';

        // Mystery box — common, rare, epic, legendary
        foreach (['common', 'rare', 'epic', 'legendary'] as $t) {
            $data[$t . '_box_cooldown'] = (int) ($raw[$t . '_box_cooldown'] ?? 0);
            $data[$t . '_box_ads'] = (int) ($raw[$t . '_box_ads'] ?? 1);
            $data[$t . '_box_min_coins'] = isset($raw[$t . '_box_min_coins']) ? (float) $raw[$t . '_box_min_coins'] : 0.0;
            $data[$t . '_box_max_coins'] = isset($raw[$t . '_box_max_coins']) ? (float) $raw[$t . '_box_max_coins'] : 0.0;
        }
        $data['legendary_box_reward_type'] = (string) ($raw['legendary_box_reward_type'] ?? 'booster');
        $data['legendary_box_booster_types'] = (string) ($raw['legendary_box_booster_types'] ?? '2x,3x,5x');
        $data['legendary_box_booster_duration'] = isset($raw['legendary_box_booster_duration']) ? (float) $raw['legendary_box_booster_duration'] : 10.0;

        // KYC
        $data['kyc_mining_sessions'] = (int) ($raw['kyc_mining_sessions'] ?? 14);
        $data['kyc_referrals_required'] = (int) ($raw['kyc_referrals_required'] ?? 10);

        // Ad waterfall
        $data['ad_waterfall_order'] = isset($raw['ad_waterfall_order']) ? $raw['ad_waterfall_order'] : null;
        $data['ad_waterfall_enabled'] = (int) (isset($raw['ad_waterfall_enabled']) ? $raw['ad_waterfall_enabled'] : 1);

        return $data;
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_type' => 'required|in:mining,referral,user_count,mystery_box,kyc,ad_waterfall',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Settings type is required.'
            ], 400);
        }

        $settings = Setting::first();
        $settingsType = $request->settings_type;

        if ($settingsType === 'mining') {
            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist(['mining_speed', 'base_mining_rate', 'max_mining_speed']);
            
            $updateData = [];
            if ($request->has('mining_speed')) $updateData['mining_speed'] = $request->mining_speed;
            if ($request->has('base_rate')) $updateData['base_mining_rate'] = $request->base_rate;
            if ($request->has('max_speed')) $updateData['max_mining_speed'] = $request->max_speed;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Mining settings updated successfully.'
            ]);

        } elseif ($settingsType === 'referral') {
            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist(['referrer_reward', 'referee_reward', 'max_referrals', 'bonus_reward']);
            
            $updateData = [];
            if ($request->has('referrer_reward')) $updateData['referrer_reward'] = $request->referrer_reward;
            if ($request->has('referee_reward')) $updateData['referee_reward'] = $request->referee_reward;
            if ($request->has('max_referrals')) $updateData['max_referrals'] = $request->max_referrals;
            if ($request->has('bonus_reward')) $updateData['bonus_reward'] = $request->bonus_reward;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Referral settings updated successfully.'
            ]);

        } elseif ($settingsType === 'user_count') {
            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist(['current_users', 'goal_users']);
            
            $updateData = [];
            if ($request->has('current_users')) $updateData['current_users'] = $request->current_users;
            if ($request->has('goal_users')) $updateData['goal_users'] = $request->goal_users;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User count updated successfully.'
            ]);

        } elseif ($settingsType === 'mystery_box') {
            $validator = Validator::make($request->all(), [
                'box_type' => 'required|in:common,rare,epic,legendary',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Box type is required.'
                ], 400);
            }

            $boxType = $request->box_type;
            $fieldPrefix = $boxType . '_box_';

            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist([
                $fieldPrefix . 'cooldown',
                $fieldPrefix . 'ads',
                $fieldPrefix . 'min_coins',
                $fieldPrefix . 'max_coins'
            ]);

            $updateData = [];
            if ($request->has('cooldown')) $updateData[$fieldPrefix . 'cooldown'] = $request->cooldown;
            if ($request->has('ads_required')) $updateData[$fieldPrefix . 'ads'] = $request->ads_required;
            if ($request->has('min_coins')) $updateData[$fieldPrefix . 'min_coins'] = $request->min_coins;
            if ($request->has('max_coins')) $updateData[$fieldPrefix . 'max_coins'] = $request->max_coins;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => ucfirst($boxType) . ' mystery box settings updated successfully.'
            ]);

        } elseif ($settingsType === 'kyc') {
            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist(['kyc_mining_sessions', 'kyc_referrals_required']);
            
            $updateData = [];
            if ($request->has('kyc_mining_sessions')) $updateData['kyc_mining_sessions'] = $request->kyc_mining_sessions;
            if ($request->has('kyc_referrals_required')) $updateData['kyc_referrals_required'] = $request->kyc_referrals_required;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'KYC settings updated successfully.'
            ]);

        } elseif ($settingsType === 'ad_waterfall') {
            // Ensure columns exist before updating (matching PHP behavior)
            $this->ensureSettingsColumnsExist(['ad_waterfall_order', 'ad_waterfall_enabled']);
            
            $updateData = [];
            if ($request->has('ad_waterfall_order')) {
                $updateData['ad_waterfall_order'] = is_array($request->ad_waterfall_order) 
                    ? json_encode($request->ad_waterfall_order) 
                    : $request->ad_waterfall_order;
            }
            if ($request->has('ad_waterfall_enabled')) $updateData['ad_waterfall_enabled'] = $request->ad_waterfall_enabled;

            Setting::updateOrCreateSettings($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Ad waterfall settings updated successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid settings type.'
        ], 400);
    }

    public function getCoinSpeedOverall(Request $request)
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist(['mining_speed', 'base_mining_rate', 'max_mining_speed']);
        
        $settings = Setting::first();

        if (!$settings) {
            return response()->json([
                'success' => true,
                'data' => [
                    'mining_speed' => 10.00,
                    'base_mining_rate' => 5.00,
                    'max_mining_speed' => 50.00
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mining_speed' => (float) ($settings->mining_speed ?? 10.00),
                'base_mining_rate' => (float) ($settings->base_mining_rate ?? 5.00),
                'max_mining_speed' => (float) ($settings->max_mining_speed ?? 50.00)
            ]
        ]);
    }

    public function updateCoinSpeedOverall(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mining_speed' => 'nullable|numeric|min:0',
            'base_mining_rate' => 'nullable|numeric|min:0',
            'max_mining_speed' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input. All values must be positive numbers.'
            ], 400);
        }

        // Ensure columns exist before updating (matching PHP behavior)
        $this->ensureSettingsColumnsExist(['mining_speed', 'base_mining_rate', 'max_mining_speed']);

        $settings = Setting::first();

        $updateData = [];
        if ($request->has('mining_speed')) $updateData['mining_speed'] = (float) $request->mining_speed;
        if ($request->has('base_mining_rate')) $updateData['base_mining_rate'] = (float) $request->base_mining_rate;
        if ($request->has('max_mining_speed')) $updateData['max_mining_speed'] = (float) $request->max_mining_speed;

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid updates provided.'
            ], 400);
        }

        $settings = Setting::updateOrCreateSettings($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Overall coin speed settings updated successfully.',
            'data' => [
                'mining_speed' => (float) $settings->mining_speed,
                'base_mining_rate' => (float) $settings->base_mining_rate,
                'max_mining_speed' => (float) $settings->max_mining_speed
            ]
        ]);
    }

    /**
     * Ensure settings table has all required columns
     * These columns are added by ALTER TABLE in SQL backup AFTER INSERT statements
     * This matches the PHP behavior where columns are added dynamically
     */
    private function ensureSettingsColumnsExist(array $columns = [])
    {
        // All possible settings columns that might be added by ALTER TABLE
        $allColumns = [
            'mining_speed' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 10.00, 'after' => 'about_us_link'],
            'base_mining_rate' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 5.00, 'after' => 'mining_speed'],
            'max_mining_speed' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 50.00, 'after' => 'base_mining_rate'],
            'referrer_reward' => ['type' => 'integer', 'params' => [], 'default' => 50, 'after' => 'max_mining_speed'],
            'referee_reward' => ['type' => 'integer', 'params' => [], 'default' => 25, 'after' => 'referrer_reward'],
            'max_referrals' => ['type' => 'integer', 'params' => [], 'default' => 100, 'after' => 'referee_reward'],
            'bonus_reward' => ['type' => 'integer', 'params' => [], 'default' => 500, 'after' => 'max_referrals'],
            'current_users' => ['type' => 'integer', 'params' => [], 'default' => 99000, 'after' => 'bonus_reward'],
            'goal_users' => ['type' => 'integer', 'params' => [], 'default' => 1000000, 'after' => 'current_users'],
            'daily_tasks_reset_time' => ['type' => 'dateTime', 'params' => [], 'default' => null, 'after' => 'goal_users'],
            'common_box_cooldown' => ['type' => 'integer', 'params' => [], 'default' => 0, 'after' => 'daily_tasks_reset_time'],
            'common_box_ads' => ['type' => 'integer', 'params' => [], 'default' => 1, 'after' => 'common_box_cooldown'],
            'common_box_min_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 1.00, 'after' => 'common_box_ads'],
            'common_box_max_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 5.00, 'after' => 'common_box_min_coins'],
            'rare_box_cooldown' => ['type' => 'integer', 'params' => [], 'default' => 5, 'after' => 'common_box_max_coins'],
            'rare_box_ads' => ['type' => 'integer', 'params' => [], 'default' => 3, 'after' => 'rare_box_cooldown'],
            'rare_box_min_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 5.00, 'after' => 'rare_box_ads'],
            'rare_box_max_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 15.00, 'after' => 'rare_box_min_coins'],
            'epic_box_cooldown' => ['type' => 'integer', 'params' => [], 'default' => 10, 'after' => 'rare_box_max_coins'],
            'epic_box_ads' => ['type' => 'integer', 'params' => [], 'default' => 6, 'after' => 'epic_box_cooldown'],
            'epic_box_min_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 15.00, 'after' => 'epic_box_ads'],
            'epic_box_max_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 50.00, 'after' => 'epic_box_min_coins'],
            'legendary_box_cooldown' => ['type' => 'integer', 'params' => [], 'default' => 30, 'after' => 'epic_box_max_coins'],
            'legendary_box_ads' => ['type' => 'integer', 'params' => [], 'default' => 10, 'after' => 'legendary_box_cooldown'],
            'legendary_box_min_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 50.00, 'after' => 'legendary_box_ads'],
            'legendary_box_max_coins' => ['type' => 'decimal', 'params' => [10, 2], 'default' => 200.00, 'after' => 'legendary_box_min_coins'],
            'kyc_mining_sessions' => ['type' => 'integer', 'params' => [], 'default' => 14, 'after' => 'legendary_box_max_coins'],
            'kyc_referrals_required' => ['type' => 'integer', 'params' => [], 'default' => 10, 'after' => 'kyc_mining_sessions'],
            'ad_waterfall_order' => ['type' => 'text', 'params' => [], 'default' => null, 'after' => 'kyc_referrals_required'],
            'ad_waterfall_enabled' => ['type' => 'boolean', 'params' => [], 'default' => 1, 'after' => 'ad_waterfall_order'],
        ];

        // If specific columns requested, only check those, otherwise check all
        $columnsToCheck = !empty($columns) ? $columns : array_keys($allColumns);

        foreach ($columnsToCheck as $column) {
            if (!isset($allColumns[$column])) {
                continue; // Skip unknown columns
            }

            if (!Schema::hasColumn('settings', $column)) {
                $config = $allColumns[$column];
                Schema::table('settings', function (Blueprint $table) use ($column, $config) {
                    $method = $config['type'];
                    $params = $config['params'];
                    $default = $config['default'];
                    $after = $config['after'];

                    // Handle boolean type specially
                    if ($method === 'boolean') {
                        $columnDefinition = $table->tinyInteger($column)->default($default ?? 0);
                    } else {
                        $columnDefinition = $table->$method($column, ...$params);
                        
                        if ($default !== null) {
                            $columnDefinition->default($default);
                        }
                    }
                    
                    $columnDefinition->nullable();
                    
                    if ($after && Schema::hasColumn('settings', $after)) {
                        $columnDefinition->after($after);
                    }
                });
            }
        }
    }
}
