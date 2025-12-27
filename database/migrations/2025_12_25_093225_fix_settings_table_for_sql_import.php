<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration removes extra columns from settings table to match SQL backup structure exactly.
     * The SQL backup only has these columns:
     * - id, update_version, maintenance, force_update, update_message, maintenance_message,
     *   update_link, pirvacy_policy_link, term_n_condition_link, support_email,
     *   faq_link, white_paper_link, road_map_link, about_us_link
     * 
     * Extra columns added by other migrations will be removed here.
     * They can be added back later if needed, or added dynamically by code.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $columnsToRemove = [
                'mining_speed',
                'base_mining_rate',
                'max_mining_speed',
                'referrer_reward',
                'referee_reward',
                'max_referrals',
                'bonus_reward',
                'current_users',
                'goal_users',
                'daily_tasks_reset_time',
                'common_box_cooldown',
                'common_box_ads',
                'common_box_min_coins',
                'common_box_max_coins',
                'rare_box_cooldown',
                'rare_box_ads',
                'rare_box_min_coins',
                'rare_box_max_coins',
                'epic_box_cooldown',
                'epic_box_ads',
                'epic_box_min_coins',
                'epic_box_max_coins',
                'legendary_box_cooldown',
                'legendary_box_ads',
                'legendary_box_min_coins',
                'legendary_box_max_coins',
                'kyc_mining_sessions',
                'kyc_referrals_required',
                'ad_waterfall_order',
                'ad_waterfall_enabled',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    Schema::table('settings', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally add columns back if needed
        // This is kept empty to preserve SQL backup compatibility
    }
};





