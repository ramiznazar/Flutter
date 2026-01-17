<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add auth_token column back to users table.
     * This column was removed by fix_all_tables_for_sql_import migration
     * but is required for API authentication.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'auth_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('auth_token', 64)->nullable()->unique()->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'auth_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('auth_token');
            });
        }
    }
};
