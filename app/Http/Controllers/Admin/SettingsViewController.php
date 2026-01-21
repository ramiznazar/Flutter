<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SettingsViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function miningSettings()
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist(['mining_speed', 'base_mining_rate', 'max_mining_speed']);
        
        $settings = Setting::first();
        $currentSettings = [
            'mining_speed' => $settings->mining_speed ?? 10,
            'base_mining_rate' => $settings->base_mining_rate ?? 5,
            'max_mining_speed' => $settings->max_mining_speed ?? 50,
        ];

        return view('admin.settings.mining', compact('currentSettings'));
    }

    public function updateMiningSettings(Request $request)
    {
        $request->validate([
            'mining_speed' => 'nullable|numeric|min:0',
            'base_rate' => 'nullable|numeric|min:0',
            'max_speed' => 'nullable|numeric|min:0',
        ]);

        // Ensure columns exist before updating (matching PHP behavior)
        $this->ensureSettingsColumnsExist(['mining_speed', 'base_mining_rate', 'max_mining_speed']);

        $settings = Setting::first();
        
        $updateData = [];
        if ($request->has('mining_speed')) $updateData['mining_speed'] = $request->mining_speed;
        if ($request->has('base_rate')) $updateData['base_mining_rate'] = $request->base_rate;
        if ($request->has('max_speed')) $updateData['max_mining_speed'] = $request->max_speed;

        if ($settings) {
            $settings->update($updateData);
        } else {
            Setting::create($updateData);
        }

        return redirect()->route('admin.mining-settings')
            ->with('message', 'Mining settings updated successfully.')
            ->with('messageType', 'success');
    }

    public function updateUserCoinSpeed(Request $request)
    {
        $request->validate([
            'user_identifier' => 'required|string',
            'coin_speed' => 'nullable|numeric|min:0',
        ]);

        $identifier = trim($request->user_identifier);

        // Find user by email, username, or ID
        $user = User::where(function($query) use ($identifier) {
            $query->where('email', $identifier)
                  ->orWhere('username', $identifier)
                  ->orWhere('id', $identifier);
        })->first();

        if (!$user) {
            return redirect()->route('admin.mining-settings')
                ->with('message', 'User not found. Please check the user ID, email, or username.')
                ->with('messageType', 'danger');
        }

        // Update custom coin speed
        if ($request->coin_speed === null || $request->coin_speed === '') {
            $user->update(['custom_coin_speed' => null]);
            $message = "Custom coin speed removed for user {$user->email}. User will now use overall settings.";
        } else {
            $user->update(['custom_coin_speed' => (float) $request->coin_speed]);
            $message = "Coin speed updated to {$request->coin_speed} for user {$user->email}.";
        }

        return redirect()->route('admin.mining-settings')
            ->with('message', $message)
            ->with('messageType', 'success');
    }

    public function referralSettings()
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist(['referrer_reward', 'referee_reward', 'max_referrals', 'bonus_reward']);
        
        $settings = Setting::first();
        $currentSettings = [
            'referrer_reward' => $settings->referrer_reward ?? 50,
            'referee_reward' => $settings->referee_reward ?? 25,
            'max_referrals' => $settings->max_referrals ?? 100,
            'bonus_reward' => $settings->bonus_reward ?? 500,
        ];

        return view('admin.settings.referral', compact('currentSettings'));
    }

    public function updateReferralSettings(Request $request)
    {
        $request->validate([
            'referrer_reward' => 'nullable|integer|min:0',
            'referee_reward' => 'nullable|integer|min:0',
            'max_referrals' => 'nullable|integer|min:0',
            'bonus_reward' => 'nullable|integer|min:0',
        ]);

        // Ensure columns exist before updating (matching PHP behavior)
        $this->ensureSettingsColumnsExist(['referrer_reward', 'referee_reward', 'max_referrals', 'bonus_reward']);

        $settings = Setting::first();
        
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

        return redirect()->route('admin.referral-settings')
            ->with('message', 'Referral settings updated successfully.')
            ->with('messageType', 'success');
    }

    public function mysteryBoxSettings()
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist([
            'common_box_cooldown', 'common_box_ads', 'common_box_min_coins', 'common_box_max_coins',
            'rare_box_cooldown', 'rare_box_ads', 'rare_box_min_coins', 'rare_box_max_coins',
            'epic_box_cooldown', 'epic_box_ads', 'epic_box_min_coins', 'epic_box_max_coins',
            'legendary_box_cooldown', 'legendary_box_ads', 'legendary_box_min_coins', 'legendary_box_max_coins',
            'legendary_box_reward_type', 'legendary_box_booster_types', 'legendary_box_booster_duration'
        ]);
        
        $settings = Setting::first();
        
        $boxSettings = [
            'common' => [
                'cooldown' => $settings->common_box_cooldown ?? 0,
                'ads' => $settings->common_box_ads ?? 1,
                'min_coins' => $settings->common_box_min_coins ?? 1.00,
                'max_coins' => $settings->common_box_max_coins ?? 5.00,
            ],
            'rare' => [
                'cooldown' => $settings->rare_box_cooldown ?? 5,
                'ads' => $settings->rare_box_ads ?? 3,
                'min_coins' => $settings->rare_box_min_coins ?? 5.00,
                'max_coins' => $settings->rare_box_max_coins ?? 15.00,
            ],
            'epic' => [
                'cooldown' => $settings->epic_box_cooldown ?? 10,
                'ads' => $settings->epic_box_ads ?? 6,
                'min_coins' => $settings->epic_box_min_coins ?? 15.00,
                'max_coins' => $settings->epic_box_max_coins ?? 50.00,
            ],
            'legendary' => [
                'cooldown' => $settings->legendary_box_cooldown ?? 30,
                'ads' => $settings->legendary_box_ads ?? 10,
                'min_coins' => $settings->legendary_box_min_coins ?? 50.00,
                'max_coins' => $settings->legendary_box_max_coins ?? 200.00,
                'reward_type' => $settings->legendary_box_reward_type ?? 'booster',
                'booster_types' => $settings->legendary_box_booster_types ?? '2x,3x,5x',
                'booster_duration' => $settings->legendary_box_booster_duration ?? 10.00,
            ],
        ];

        return view('admin.settings.mystery-box', compact('boxSettings'));
    }

    public function updateMysteryBoxSettings(Request $request)
    {
        $boxType = $request->box_type;
        
        if ($boxType === 'legendary') {
            $request->validate([
                'box_type' => 'required|in:common,rare,epic,legendary',
                'cooldown' => 'required|integer|min:0',
                'ads_required' => 'required|integer|min:1',
                'min_coins' => 'nullable|numeric|min:0',
                'max_coins' => 'nullable|numeric|min:0',
                'reward_type' => 'required|in:coins,booster',
                'booster_types' => 'required_if:reward_type,booster|string',
                'booster_duration' => 'required_if:reward_type,booster|numeric|min:0.1|max:168',
            ]);
        } else {
            $request->validate([
                'box_type' => 'required|in:common,rare,epic,legendary',
                'cooldown' => 'required|integer|min:0',
                'ads_required' => 'required|integer|min:1',
                'min_coins' => 'required|numeric|min:0',
                'max_coins' => 'required|numeric|min:0',
            ]);
        }

        $fieldPrefix = $boxType . '_box_';

        // Ensure columns exist before updating
        $columnsToCheck = [
            $fieldPrefix . 'cooldown',
            $fieldPrefix . 'ads',
            $fieldPrefix . 'min_coins',
            $fieldPrefix . 'max_coins'
        ];
        
        if ($boxType === 'legendary') {
            $columnsToCheck = array_merge($columnsToCheck, [
                'legendary_box_reward_type',
                'legendary_box_booster_types',
                'legendary_box_booster_duration'
            ]);
        }
        
        $this->ensureSettingsColumnsExist($columnsToCheck);

        $updateData = [
            $fieldPrefix . 'cooldown' => $request->cooldown,
            $fieldPrefix . 'ads' => $request->ads_required,
        ];
        
        // For legendary box, handle reward type
        if ($boxType === 'legendary') {
            $updateData['legendary_box_reward_type'] = $request->reward_type ?? 'booster';
            
            if ($request->reward_type === 'booster') {
                // Booster mode - validate and set booster settings
                $boosterTypes = $request->booster_types ?? '2x,3x,5x';
                // Clean and validate booster types
                $boosterTypesArray = array_map('trim', explode(',', $boosterTypes));
                $boosterTypesArray = array_filter($boosterTypesArray);
                $validBoosterTypes = ['2x', '3x', '5x'];
                $boosterTypesArray = array_intersect($boosterTypesArray, $validBoosterTypes);
                
                if (empty($boosterTypesArray)) {
                    $boosterTypesArray = ['2x', '3x', '5x']; // Default fallback
                }
                
                $updateData['legendary_box_booster_types'] = implode(',', $boosterTypesArray);
                $updateData['legendary_box_booster_duration'] = (float) ($request->booster_duration ?? 10.00);
                // Keep min/max coins for reference but they won't be used
                $updateData[$fieldPrefix . 'min_coins'] = $request->min_coins ?? 0;
                $updateData[$fieldPrefix . 'max_coins'] = $request->max_coins ?? 0;
            } else {
                // Coins mode - use min/max coins
                $updateData[$fieldPrefix . 'min_coins'] = $request->min_coins ?? 50.00;
                $updateData[$fieldPrefix . 'max_coins'] = $request->max_coins ?? 200.00;
            }
        } else {
            // Other boxes - always use coins
            $updateData[$fieldPrefix . 'min_coins'] = $request->min_coins;
            $updateData[$fieldPrefix . 'max_coins'] = $request->max_coins;
        }

        $settings = Setting::first();
        if ($settings) {
            $settings->update($updateData);
        } else {
            Setting::create($updateData);
        }

        return redirect()->route('admin.mystery-box')
            ->with('message', ucfirst($boxType) . ' mystery box settings updated successfully.')
            ->with('messageType', 'success');
    }

    public function kycSettings()
    {
        // Ensure columns exist before accessing them
        $this->ensureSettingsColumnsExist(['kyc_mining_sessions', 'kyc_referrals_required']);
        
        $settings = Setting::first();
        $currentSettings = [
            'kyc_mining_sessions' => $settings->kyc_mining_sessions ?? 14,
            'kyc_referrals_required' => $settings->kyc_referrals_required ?? 10,
        ];

        return view('admin.settings.kyc', compact('currentSettings'));
    }

    public function updateKycSettings(Request $request)
    {
        $request->validate([
            'kyc_mining_sessions' => 'nullable|integer|min:0',
            'kyc_referrals_required' => 'nullable|integer|min:0',
        ]);

        // Ensure columns exist before updating (matching PHP behavior)
        $this->ensureSettingsColumnsExist(['kyc_mining_sessions', 'kyc_referrals_required']);

        $settings = Setting::first();
        
        $updateData = [];
        if ($request->has('kyc_mining_sessions')) $updateData['kyc_mining_sessions'] = $request->kyc_mining_sessions;
        if ($request->has('kyc_referrals_required')) $updateData['kyc_referrals_required'] = $request->kyc_referrals_required;

        if ($settings) {
            $settings->update($updateData);
        } else {
            Setting::create($updateData);
        }

        return redirect()->route('admin.kyc-settings')
            ->with('message', 'KYC settings updated successfully.')
            ->with('messageType', 'success');
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
            'legendary_box_reward_type' => ['type' => 'string', 'params' => [50], 'default' => 'booster', 'after' => 'legendary_box_max_coins'],
            'legendary_box_booster_types' => ['type' => 'string', 'params' => [50], 'default' => '2x,3x,5x', 'after' => 'legendary_box_reward_type'],
            'legendary_box_booster_duration' => ['type' => 'decimal', 'params' => [5, 2], 'default' => 10.00, 'after' => 'legendary_box_booster_types'],
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















