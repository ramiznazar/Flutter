<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mining_start_balance')) {
                $table->decimal('mining_start_balance', 20, 10)->nullable()->after('token')
                    ->comment('Balance when mining started (for accurate calculation)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mining_start_balance')) {
                $table->dropColumn('mining_start_balance');
            }
        });
    }
};
