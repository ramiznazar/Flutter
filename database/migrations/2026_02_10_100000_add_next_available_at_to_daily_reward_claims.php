<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds the next_available_at column to daily_reward_claims table with a default value.
     */
    public function up(): void
    {
        Schema::table('daily_reward_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_reward_claims', 'next_available_at')) {
                $table->dateTime('next_available_at')
                    ->default(DB::raw('CURRENT_TIMESTAMP'))
                    ->after('claimed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reward_claims', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reward_claims', 'next_available_at')) {
                $table->dropColumn('next_available_at');
            }
        });
    }
};
