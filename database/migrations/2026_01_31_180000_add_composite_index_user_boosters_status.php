<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Composite index for booster_status / mining_status active-booster lookup (user_id, is_active, expires_at).
     * Safe for re-run: catches "Duplicate key name" so migration does not crash in production.
     */
    public function up(): void
    {
        try {
            Schema::table('user_boosters', function (Blueprint $table) {
                $table->index(['user_id', 'is_active', 'expires_at'], 'user_boosters_status_idx');
            });
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate') !== false || stripos($msg, 'already exists') !== false) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('user_boosters', function (Blueprint $table) {
                $table->dropIndex('user_boosters_status_idx');
            });
        } catch (\Throwable $e) {
            // Index may not exist
        }
    }
};
