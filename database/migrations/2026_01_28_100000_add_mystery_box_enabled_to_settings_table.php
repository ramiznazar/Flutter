<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-box "enabled" flags so admin can choose which mystery box types
     * to show in the app. Disabled types are not included in the API response.
     */
    public function up(): void
    {
        $tableName = 'settings';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        $columns = [
            'common_box_enabled'   => ['after' => 'common_box_max_coins'],
            'rare_box_enabled'     => ['after' => 'rare_box_max_coins'],
            'epic_box_enabled'     => ['after' => 'epic_box_max_coins'],
            'legendary_box_enabled' => ['after' => 'legendary_box_max_coins'],
        ];

        foreach ($columns as $col => $config) {
            if (Schema::hasColumn($tableName, $col)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($col, $config) {
                $def = $table->unsignedTinyInteger($col)->default(1);
                $after = $config['after'] ?? null;
                if ($after && Schema::hasColumn('settings', $after)) {
                    $def->after($after);
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }
        Schema::table('settings', function (Blueprint $table) {
            foreach (['common_box_enabled', 'rare_box_enabled', 'epic_box_enabled', 'legendary_box_enabled'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
