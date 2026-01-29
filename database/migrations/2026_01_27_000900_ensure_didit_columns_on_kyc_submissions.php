<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure Didit-related columns exist on kyc_submissions.
     *
     * This is idempotent and safe to run even if the columns already exist.
     * It fixes cases where an earlier migration (fix_all_tables_for_sql_import)
     * dropped these columns but the app and API still rely on them.
     */
    public function up(): void
    {
        $tableName = 'kyc_submissions';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'didit_request_id')) {
                $table->string('didit_request_id', 255)->nullable()->after('admin_notes');
            }
            if (!Schema::hasColumn($tableName, 'didit_status')) {
                $table->string('didit_status', 50)->nullable()->after('didit_request_id');
            }
            if (!Schema::hasColumn($tableName, 'didit_verification_data')) {
                $table->text('didit_verification_data')->nullable()->after('didit_status');
            }
            if (!Schema::hasColumn($tableName, 'didit_verified_at')) {
                $table->dateTime('didit_verified_at')->nullable()->after('didit_verification_data');
            }
        });

        // Add indexes for didit_request_id and didit_status if missing
        foreach (['didit_request_id', 'didit_status'] as $col) {
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
     * Optionally drop Didit-related columns.
     */
    public function down(): void
    {
        $tableName = 'kyc_submissions';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $columns = ['didit_request_id', 'didit_status', 'didit_verification_data', 'didit_verified_at'];

            // Drop indexes first where present
            foreach (['didit_request_id', 'didit_status'] as $col) {
                $indexName = 'kyc_submissions_' . $col . '_index';
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $e) {
                    // Ignore if index does not exist
                }
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn('kyc_submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

