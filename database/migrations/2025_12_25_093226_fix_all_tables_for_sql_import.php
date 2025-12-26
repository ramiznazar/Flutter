<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration removes extra columns from tables to match SQL backup structure exactly.
     * The SQL backup (crutox_backup_reduced.sql) has:
     * 1. CREATE TABLE statements (base structure)
     * 2. INSERT statements (data with base structure)
     * 3. ALTER TABLE statements (add columns AFTER INSERT)
     * 
     * So columns added by Laravel migrations should NOT exist before importing the SQL backup.
     * They will be added by the ALTER TABLE statements in the SQL backup AFTER import.
     * 
     * Columns to remove (added by migrations but should be added AFTER SQL import):
     * - shop: Description, Price (added by ALTER TABLE in SQL backup line 1012-1013)
     * - social_media_setting: task_type, Status (added by ALTER TABLE in SQL backup line 1001-1002)
     * - giveaway: reward, start_date, end_date, status, redirect_link (added by ALTER TABLE in SQL backup line 1005-1009)
     * - mystery_box_claims: clicks, last_clicked_at (NOT in SQL backup CREATE TABLE, added by separate migration file)
     * - kyc_submissions: didit_request_id, didit_status, didit_verification_data, didit_verified_at (NOT in SQL backup, added by separate migration file)
     * - users: auth_token, custom_coin_speed (NOT in SQL backup CREATE TABLE, added by Laravel migrations)
     */
    public function up(): void
    {
        // Remove Description and Price from shop table
        // These are added by ALTER TABLE in SQL backup AFTER INSERT statements
        if (Schema::hasTable('shop')) {
            if (Schema::hasColumn('shop', 'Description')) {
                Schema::table('shop', function (Blueprint $table) {
                    $table->dropColumn('Description');
                });
            }
            if (Schema::hasColumn('shop', 'Price')) {
                Schema::table('shop', function (Blueprint $table) {
                    $table->dropColumn('Price');
                });
            }
        }

        // Remove task_type and Status from social_media_setting table
        // These are added by ALTER TABLE in SQL backup AFTER INSERT statements
        if (Schema::hasTable('social_media_setting')) {
            if (Schema::hasColumn('social_media_setting', 'task_type')) {
                Schema::table('social_media_setting', function (Blueprint $table) {
                    $table->dropColumn('task_type');
                });
            }
            if (Schema::hasColumn('social_media_setting', 'Status')) {
                Schema::table('social_media_setting', function (Blueprint $table) {
                    $table->dropColumn('Status');
                });
            }
        }

        // Remove extra columns from giveaway table
        // These are added by ALTER TABLE in SQL backup AFTER INSERT statements
        if (Schema::hasTable('giveaway')) {
            $columnsToRemove = ['reward', 'start_date', 'end_date', 'status', 'redirect_link'];
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('giveaway', $column)) {
                    Schema::table('giveaway', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        // Remove clicks and last_clicked_at from mystery_box_claims
        // These are NOT in the SQL backup CREATE TABLE (line 1137-1154)
        // They are added by database_migration_mystery_box_clicks.sql AFTER import
        if (Schema::hasTable('mystery_box_claims')) {
            if (Schema::hasColumn('mystery_box_claims', 'clicks')) {
                Schema::table('mystery_box_claims', function (Blueprint $table) {
                    $table->dropColumn('clicks');
                });
            }
            if (Schema::hasColumn('mystery_box_claims', 'last_clicked_at')) {
                Schema::table('mystery_box_claims', function (Blueprint $table) {
                    $table->dropColumn('last_clicked_at');
                });
            }
        }

        // Remove Didit columns from kyc_submissions
        // These are NOT in the SQL backup CREATE TABLE (line 1048-1063)
        // They are added by database_migration_didit_kyc.sql AFTER import
        if (Schema::hasTable('kyc_submissions')) {
            $columnsToRemove = ['didit_request_id', 'didit_status', 'didit_verification_data', 'didit_verified_at'];
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('kyc_submissions', $column)) {
                    Schema::table('kyc_submissions', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        // Remove auth_token and custom_coin_speed from users
        // These are NOT in the SQL backup CREATE TABLE (line 840-865)
        // They are added by Laravel migrations for API functionality
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'auth_token')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('auth_token');
                });
            }
            if (Schema::hasColumn('users', 'custom_coin_speed')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('custom_coin_speed');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally add columns back if needed
        // This is kept empty to preserve SQL backup compatibility
    }
};

