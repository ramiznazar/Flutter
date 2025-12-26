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
    public function index(Request $request)
    {
        $settings = Setting::first();

        if (!$settings) {
            // Return default settings
            return response()->json([
                'success' => true,
                'data' => [
                    'mining_speed' => 10,
                    'base_mining_rate' => 5,
                    'max_mining_speed' => 50,
                    'referrer_reward' => 50,
                    'referee_reward' => 25,
                    'max_referrals' => 100,
                    'bonus_reward' => 500,
                    'current_users' => 99000,
                    'goal_users' => 1000000
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

            if ($settings) {
                $settings->update($updateData);
            } else {
                Setting::create($updateData);
            }

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

        if ($settings) {
            $settings->update($updateData);
        } else {
            $settings = Setting::create($updateData);
        }

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
