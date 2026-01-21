<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    
    public $timestamps = false; // Disable timestamps for settings table

    protected $fillable = [
        'update_version', 'maintenance', 'force_update', 'update_message',
        'maintenance_message', 'update_link', 'pirvacy_policy_link',
        'term_n_condition_link', 'support_email', 'faq_link',
        'white_paper_link', 'road_map_link', 'about_us_link',
        'mining_speed', 'base_mining_rate', 'max_mining_speed',
        'referrer_reward', 'referee_reward', 'max_referrals', 'bonus_reward',
        'current_users', 'goal_users', 'daily_tasks_reset_time',
        'common_box_cooldown', 'common_box_ads', 'common_box_min_coins', 'common_box_max_coins',
        'rare_box_cooldown', 'rare_box_ads', 'rare_box_min_coins', 'rare_box_max_coins',
        'epic_box_cooldown', 'epic_box_ads', 'epic_box_min_coins', 'epic_box_max_coins',
        'legendary_box_cooldown', 'legendary_box_ads', 'legendary_box_min_coins', 'legendary_box_max_coins',
        'legendary_box_reward_type', 'legendary_box_booster_types', 'legendary_box_booster_duration',
        'kyc_mining_sessions', 'kyc_referrals_required',
        'ad_waterfall_order', 'ad_waterfall_enabled'
    ];
}
