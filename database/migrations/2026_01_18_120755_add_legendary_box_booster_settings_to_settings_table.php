<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotent and works even when legendary_box_max_coins does not exist (e.g. settings from dump).
     */
    public function up(): void
    {
        $t = 'settings';
        $afterCol = Schema::hasColumn($t, 'legendary_box_max_coins') ? 'legendary_box_max_coins' : 'about_us_link';

        if (!Schema::hasColumn($t, 'legendary_box_reward_type')) {
            Schema::table($t, function (Blueprint $table) use ($afterCol) {
                $table->string('legendary_box_reward_type', 20)->default('booster')->nullable()->after($afterCol)->comment('Reward type for legendary box: coins or booster');
            });
            $afterCol = 'legendary_box_reward_type';
        } elseif (Schema::hasColumn($t, 'legendary_box_reward_type')) {
            $afterCol = 'legendary_box_reward_type';
        }

        if (!Schema::hasColumn($t, 'legendary_box_booster_types')) {
            Schema::table($t, function (Blueprint $table) use ($afterCol) {
                $table->string('legendary_box_booster_types', 50)->default('2x,3x,5x')->nullable()->after($afterCol)->comment('Available booster types for legendary box (comma-separated: 2x,3x,5x)');
            });
            $afterCol = 'legendary_box_booster_types';
        } elseif (Schema::hasColumn($t, 'legendary_box_booster_types')) {
            $afterCol = 'legendary_box_booster_types';
        }

        if (!Schema::hasColumn($t, 'legendary_box_booster_duration')) {
            Schema::table($t, function (Blueprint $table) use ($afterCol) {
                $table->decimal('legendary_box_booster_duration', 5, 2)->default(10.00)->nullable()->after($afterCol)->comment('Booster duration in hours for legendary box');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'legendary_box_booster_duration')) {
                $table->dropColumn('legendary_box_booster_duration');
            }
            if (Schema::hasColumn('settings', 'legendary_box_booster_types')) {
                $table->dropColumn('legendary_box_booster_types');
            }
            if (Schema::hasColumn('settings', 'legendary_box_reward_type')) {
                $table->dropColumn('legendary_box_reward_type');
            }
        });
    }
};
