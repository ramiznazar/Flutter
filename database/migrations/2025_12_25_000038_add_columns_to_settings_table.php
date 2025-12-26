<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('mining_speed', 10, 2)->default(10.00)->nullable()->after('about_us_link');
            $table->decimal('base_mining_rate', 10, 2)->default(5.00)->nullable()->after('mining_speed');
            $table->decimal('max_mining_speed', 10, 2)->default(50.00)->nullable()->after('base_mining_rate');
            $table->integer('referrer_reward')->default(50)->nullable()->after('max_mining_speed');
            $table->integer('referee_reward')->default(25)->nullable()->after('referrer_reward');
            $table->integer('max_referrals')->default(100)->nullable()->after('referee_reward');
            $table->integer('bonus_reward')->default(500)->nullable()->after('max_referrals');
            $table->integer('current_users')->default(99000)->nullable()->after('bonus_reward');
            $table->integer('goal_users')->default(1000000)->nullable()->after('current_users');
            $table->dateTime('daily_tasks_reset_time')->nullable()->after('goal_users');
            $table->integer('common_box_cooldown')->default(5)->nullable()->after('daily_tasks_reset_time');
            $table->integer('common_box_ads')->default(1)->nullable()->after('common_box_cooldown');
            $table->decimal('common_box_min_coins', 10, 2)->default(1.00)->nullable()->after('common_box_ads');
            $table->decimal('common_box_max_coins', 10, 2)->default(5.00)->nullable()->after('common_box_min_coins');
            $table->integer('rare_box_cooldown')->default(5)->nullable()->after('common_box_max_coins');
            $table->integer('rare_box_ads')->default(3)->nullable()->after('rare_box_cooldown');
            $table->decimal('rare_box_min_coins', 10, 2)->default(5.00)->nullable()->after('rare_box_ads');
            $table->decimal('rare_box_max_coins', 10, 2)->default(15.00)->nullable()->after('rare_box_min_coins');
            $table->integer('epic_box_cooldown')->default(10)->nullable()->after('rare_box_max_coins');
            $table->integer('epic_box_ads')->default(6)->nullable()->after('epic_box_cooldown');
            $table->decimal('epic_box_min_coins', 10, 2)->default(15.00)->nullable()->after('epic_box_ads');
            $table->decimal('epic_box_max_coins', 10, 2)->default(50.00)->nullable()->after('epic_box_min_coins');
            $table->integer('legendary_box_cooldown')->default(30)->nullable()->after('epic_box_max_coins');
            $table->integer('legendary_box_ads')->default(10)->nullable()->after('legendary_box_cooldown');
            $table->decimal('legendary_box_min_coins', 10, 2)->default(50.00)->nullable()->after('legendary_box_ads');
            $table->decimal('legendary_box_max_coins', 10, 2)->default(200.00)->nullable()->after('legendary_box_min_coins');
            $table->integer('kyc_mining_sessions')->default(14)->nullable()->after('legendary_box_max_coins');
            $table->integer('kyc_referrals_required')->default(10)->nullable()->after('kyc_mining_sessions');
            $table->text('ad_waterfall_order')->nullable()->comment('JSON array: ["admob", "meta", "unity", "applovin"]')->after('kyc_referrals_required');
            $table->boolean('ad_waterfall_enabled')->default(1)->nullable()->after('ad_waterfall_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'mining_speed', 'base_mining_rate', 'max_mining_speed',
                'referrer_reward', 'referee_reward', 'max_referrals', 'bonus_reward',
                'current_users', 'goal_users', 'daily_tasks_reset_time',
                'common_box_cooldown', 'common_box_ads', 'common_box_min_coins', 'common_box_max_coins',
                'rare_box_cooldown', 'rare_box_ads', 'rare_box_min_coins', 'rare_box_max_coins',
                'epic_box_cooldown', 'epic_box_ads', 'epic_box_min_coins', 'epic_box_max_coins',
                'legendary_box_cooldown', 'legendary_box_ads', 'legendary_box_min_coins', 'legendary_box_max_coins',
                'kyc_mining_sessions', 'kyc_referrals_required',
                'ad_waterfall_order', 'ad_waterfall_enabled'
            ]);
        });
    }
};
