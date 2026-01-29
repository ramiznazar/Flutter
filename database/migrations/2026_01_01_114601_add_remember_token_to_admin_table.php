<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotent: safe to run when column already exists.
     */
    public function up(): void
    {
        if (Schema::hasColumn('admin', 'remember_token')) {
            return;
        }
        Schema::table('admin', function (Blueprint $table) {
            $table->rememberToken()->nullable()->after('last_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropRememberToken();
        });
    }
};
