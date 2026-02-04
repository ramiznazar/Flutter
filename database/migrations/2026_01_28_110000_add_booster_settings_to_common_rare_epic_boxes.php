<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add reward_type, booster_types, booster_duration for common, rare, epic boxes
     * (legendary already has these). Default: booster, 2x,3x,5x, 10 hours.
     */
    public function up(): void
    {
        $tableName = 'settings';
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $defs = [
            ['common_box_reward_type', 'string', 50, 'booster', 'common_box_max_coins'],
            ['common_box_booster_types', 'string', 50, '2x,3x,5x', 'common_box_reward_type'],
            ['common_box_booster_duration', 'decimal', 10.00, null, 'common_box_booster_types'],
            ['rare_box_reward_type', 'string', 50, 'booster', 'rare_box_max_coins'],
            ['rare_box_booster_types', 'string', 50, '2x,3x,5x', 'rare_box_reward_type'],
            ['rare_box_booster_duration', 'decimal', 10.00, null, 'rare_box_booster_types'],
            ['epic_box_reward_type', 'string', 50, 'booster', 'epic_box_max_coins'],
            ['epic_box_booster_types', 'string', 50, '2x,3x,5x', 'epic_box_reward_type'],
            ['epic_box_booster_duration', 'decimal', 10.00, null, 'epic_box_booster_types'],
        ];

        foreach ($defs as $d) {
            [$name, $type, $param, $default, $afterCol] = $d;
            if (Schema::hasColumn($tableName, $name)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($name, $type, $param, $default, $afterCol) {
                if ($type === 'string') {
                    $def = $table->string($name, $param)->default($default);
                } else {
                    $def = $table->decimal($name, 5, 2)->default($param);
                }
                if ($afterCol && Schema::hasColumn('settings', $afterCol)) {
                    $def->after($afterCol);
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }
        $cols = [];
        foreach (['common', 'rare', 'epic'] as $type) {
            $cols[] = "{$type}_box_reward_type";
            $cols[] = "{$type}_box_booster_types";
            $cols[] = "{$type}_box_booster_duration";
        }
        Schema::table('settings', function (Blueprint $table) use ($cols) {
            foreach ($cols as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
