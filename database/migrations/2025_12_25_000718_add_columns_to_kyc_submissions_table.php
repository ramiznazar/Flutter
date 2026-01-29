<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotent: safe to run when columns/indexes already exist.
     */
    public function up(): void
    {
        $tableName = 'kyc_submissions';

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

        foreach (['didit_request_id', 'didit_status'] as $col) {
            if (!Schema::hasColumn($tableName, $col)) {
                continue;
            }
            $idx = $tableName . '_' . $col . '_index';
            $exists = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
                [$tableName, $idx]
            );
            if (!$exists) {
                Schema::table($tableName, fn (Blueprint $t) => $t->index($col));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_submissions', function (Blueprint $table) {
            $table->dropIndex(['didit_request_id', 'didit_status']);
            $table->dropColumn(['didit_request_id', 'didit_status', 'didit_verification_data', 'didit_verified_at']);
        });
    }
};
