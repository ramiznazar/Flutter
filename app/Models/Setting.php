<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'id', // allow when creating the single row via updateOrCreate
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

    /**
     * Default values for all NOT NULL columns when creating the single settings row.
     * Used so updateOrCreateSettings() never fails with "field doesn't have a default value".
     * Covers every fillable column so inserts work whether the table came from dump or migrations.
     */
    public static function defaultAttributes(): array
    {
        return [
            'update_version'            => '1.0.0',
            'maintenance'               => '0',
            'force_update'              => '0',
            'update_message'             => '',
            'maintenance_message'       => '.',
            'update_link'               => '',
            'pirvacy_policy_link'       => '',
            'term_n_condition_link'     => '',
            'support_email'             => '',
            'faq_link'                  => '',
            'white_paper_link'          => '',
            'road_map_link'             => '',
            'about_us_link'             => '',
            'mining_speed'              => 10.00,
            'base_mining_rate'          => 5.00,
            'max_mining_speed'          => 50.00,
            'referrer_reward'           => 50,
            'referee_reward'            => 25,
            'max_referrals'             => 100,
            'bonus_reward'              => 500,
            'current_users'             => 99000,
            'goal_users'                => 1000000,
            'daily_tasks_reset_time'    => null,
            'common_box_cooldown'       => 5,
            'common_box_ads'            => 1,
            'common_box_min_coins'      => 1.00,
            'common_box_max_coins'      => 5.00,
            'rare_box_cooldown'         => 5,
            'rare_box_ads'              => 3,
            'rare_box_min_coins'        => 5.00,
            'rare_box_max_coins'        => 15.00,
            'epic_box_cooldown'         => 10,
            'epic_box_ads'              => 6,
            'epic_box_min_coins'        => 15.00,
            'epic_box_max_coins'        => 50.00,
            'legendary_box_cooldown'    => 30,
            'legendary_box_ads'         => 10,
            'legendary_box_min_coins'   => 50.00,
            'legendary_box_max_coins'   => 200.00,
            'legendary_box_reward_type' => 'booster',
            'legendary_box_booster_types' => '2x,3x,5x',
            'legendary_box_booster_duration' => 10.00,
            'kyc_mining_sessions'       => 14,
            'kyc_referrals_required'    => 10,
            'ad_waterfall_order'        => null,
            'ad_waterfall_enabled'      => 1,
        ];
    }

    /**
     * Update or create the single settings row (id=1).
     * - When the row exists: only the keys in $data are updated (partial update), so other
     *   sections (e.g. mining vs referral) are never overwritten.
     * - When the row does not exist: merges defaultAttributes() with $data so NOT NULL
     *   columns get values, then creates.
     * Only touches columns that exist on the table (dump vs migrated schema safe).
     */
    public static function updateOrCreateSettings(array $data): self
    {
        $columns = Schema::getColumnListing((new self)->getTable());
        $existing = self::find(1);

        if ($existing) {
            $toUpdate = array_intersect_key($data, array_flip($columns));
            if (!empty($toUpdate)) {
                $existing->update($toUpdate);
            }
            return $existing;
        }

        $merged = array_merge(self::defaultAttributes(), $data);
        $merged['id'] = 1;
        $filtered = array_intersect_key($merged, array_flip($columns));
        return self::create($filtered);
    }
}
