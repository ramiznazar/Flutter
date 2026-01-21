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
            if (!Schema::hasColumn('settings', 'legendary_box_reward_type')) {
                $table->string('legendary_box_reward_type', 20)->default('booster')->nullable()->after('legendary_box_max_coins')->comment('Reward type for legendary box: coins or booster');
            }
            if (!Schema::hasColumn('settings', 'legendary_box_booster_types')) {
                $table->string('legendary_box_booster_types', 50)->default('2x,3x,5x')->nullable()->after('legendary_box_reward_type')->comment('Available booster types for legendary box (comma-separated: 2x,3x,5x)');
            }
            if (!Schema::hasColumn('settings', 'legendary_box_booster_duration')) {
                $table->decimal('legendary_box_booster_duration', 5, 2)->default(10.00)->nullable()->after('legendary_box_booster_types')->comment('Booster duration in hours for legendary box');
            }
        });
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
