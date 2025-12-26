<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Based on crutox_backup_reduced.sql analysis:
     * - Only add timestamps to tables that have them in the SQL backup
     * - Tables with created_at only: task_completions, user_boosters, mystery_box_claims
     * - Tables with created_at and updated_at: kyc_submissions, giveaway
     * - All other tables do NOT have timestamps and should NOT get them
     */
    public function up(): void
    {
        // giveaway - has created_at timestamp (from SQL)
        if (Schema::hasTable('giveaway') && !Schema::hasColumn('giveaway', 'created_at')) {
            Schema::table('giveaway', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        // kyc_submissions - has created_at and updated_at DATETIME (from SQL)
        if (Schema::hasTable('kyc_submissions')) {
            if (!Schema::hasColumn('kyc_submissions', 'created_at')) {
                Schema::table('kyc_submissions', function (Blueprint $table) {
                    $table->dateTime('created_at')->nullable();
                });
            }
            if (!Schema::hasColumn('kyc_submissions', 'updated_at')) {
                Schema::table('kyc_submissions', function (Blueprint $table) {
                    $table->dateTime('updated_at')->nullable();
                });
            }
        }

        // task_completions - has created_at DATETIME only (NO updated_at in SQL)
        if (Schema::hasTable('task_completions') && !Schema::hasColumn('task_completions', 'created_at')) {
            Schema::table('task_completions', function (Blueprint $table) {
                $table->dateTime('created_at')->nullable();
            });
        }

        // user_boosters - has created_at DATETIME only (NO updated_at in SQL)
        if (Schema::hasTable('user_boosters') && !Schema::hasColumn('user_boosters', 'created_at')) {
            Schema::table('user_boosters', function (Blueprint $table) {
                $table->dateTime('created_at')->nullable();
            });
        }

        // mystery_box_claims - has created_at DATETIME only (NO updated_at in SQL)
        if (Schema::hasTable('mystery_box_claims') && !Schema::hasColumn('mystery_box_claims', 'created_at')) {
            Schema::table('mystery_box_claims', function (Blueprint $table) {
                $table->dateTime('created_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove timestamps if needed
        // This is kept empty to preserve data
    }
};

