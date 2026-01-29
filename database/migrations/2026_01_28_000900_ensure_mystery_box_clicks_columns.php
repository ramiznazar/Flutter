<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure clicks / last_clicked_at exist on mystery_box_claims.
     *
     * Fixes: SQLSTATE[42S22]: Unknown column 'clicks' in 'field list'
     * when inserting into mystery_box_claims from MysteryBoxController::click().
     *
     * Idempotent and safe to run multiple times.
     */
    public function up(): void
    {
        $tableName = 'mystery_box_claims';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'clicks')) {
                $table->integer('clicks')->default(0)->nullable()->after('box_type');
            }
            if (!Schema::hasColumn($tableName, 'last_clicked_at')) {
                $table->dateTime('last_clicked_at')->nullable()->after('clicks');
            }
        });

        // Add indexes for clicks and last_clicked_at if missing
        foreach (['clicks', 'last_clicked_at'] as $col) {
            if (!Schema::hasColumn($tableName, $col)) {
                continue;
            }

            $indexName = $tableName . '_' . $col . '_index';

            $exists = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
                [$tableName, $indexName]
            );

            if (!$exists) {
                Schema::table($tableName, function (Blueprint $t) use ($col) {
                    $t->index($col);
                });
            }
        }
    }

    /**
     * Optionally drop the columns and indexes.
     */
    public function down(): void
    {
        $tableName = 'mystery_box_claims';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            foreach (['clicks', 'last_clicked_at'] as $col) {
                $indexName = 'mystery_box_claims_' . $col . '_index';
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $e) {
                    // Ignore if index does not exist
                }
            }

            foreach (['clicks', 'last_clicked_at'] as $col) {
                if (Schema::hasColumn('mystery_box_claims', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

