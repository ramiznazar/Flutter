<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ad Booster: user watches ad to get a speed booster (e.g. 3x for 1hr). Max 3 per day, 8hr cooldown between claims.
     */
    public function up(): void
    {
        $settingsTable = 'settings';
        if (Schema::hasTable($settingsTable)) {
            if (!Schema::hasColumn($settingsTable, 'ad_booster_enabled')) {
                Schema::table($settingsTable, fn (Blueprint $t) => $t->boolean('ad_booster_enabled')->default(0)->nullable());
            }
            if (!Schema::hasColumn($settingsTable, 'ad_booster_cooldown_hours')) {
                Schema::table($settingsTable, fn (Blueprint $t) => $t->decimal('ad_booster_cooldown_hours', 5, 2)->default(8)->nullable());
            }
            if (!Schema::hasColumn($settingsTable, 'ad_booster_duration_hours')) {
                Schema::table($settingsTable, fn (Blueprint $t) => $t->decimal('ad_booster_duration_hours', 5, 2)->default(1)->nullable());
            }
            if (!Schema::hasColumn($settingsTable, 'ad_booster_type')) {
                Schema::table($settingsTable, fn (Blueprint $t) => $t->string('ad_booster_type', 20)->default('3x')->nullable());
            }
            if (!Schema::hasColumn($settingsTable, 'ad_booster_max_per_day')) {
                Schema::table($settingsTable, fn (Blueprint $t) => $t->unsignedTinyInteger('ad_booster_max_per_day')->default(3)->nullable());
            }
        }

        if (!Schema::hasTable('ad_booster_claims')) {
            Schema::create('ad_booster_claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->dateTime('claimed_at');
                $table->index(['user_id', 'claimed_at']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_booster_claims');
        $t = 'settings';
        if (Schema::hasTable($t)) {
            $cols = ['ad_booster_enabled', 'ad_booster_cooldown_hours', 'ad_booster_duration_hours', 'ad_booster_type', 'ad_booster_max_per_day'];
            foreach ($cols as $col) {
                if (Schema::hasColumn($t, $col)) {
                    Schema::table($t, fn (Blueprint $table) => $table->dropColumn($col));
                }
            }
        }
    }
};
