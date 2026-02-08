<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared settings table (gamification: mystery box + ad booster columns only).
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'common_box_cooldown', 'common_box_ads', 'common_box_min_coins', 'common_box_max_coins', 'common_box_enabled',
        'common_box_reward_type', 'common_box_booster_types', 'common_box_booster_duration',
        'rare_box_cooldown', 'rare_box_ads', 'rare_box_min_coins', 'rare_box_max_coins', 'rare_box_enabled',
        'rare_box_reward_type', 'rare_box_booster_types', 'rare_box_booster_duration',
        'epic_box_cooldown', 'epic_box_ads', 'epic_box_min_coins', 'epic_box_max_coins', 'epic_box_enabled',
        'epic_box_reward_type', 'epic_box_booster_types', 'epic_box_booster_duration',
        'legendary_box_cooldown', 'legendary_box_ads', 'legendary_box_min_coins', 'legendary_box_max_coins', 'legendary_box_enabled',
        'legendary_box_reward_type', 'legendary_box_booster_types', 'legendary_box_booster_duration',
        'ad_booster_enabled', 'ad_booster_cooldown_hours', 'ad_booster_duration_hours', 'ad_booster_type', 'ad_booster_max_per_day',
    ];
}
