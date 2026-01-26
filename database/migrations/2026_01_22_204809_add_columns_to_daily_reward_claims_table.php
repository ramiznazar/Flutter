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
        Schema::table('daily_reward_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->decimal('coins_claimed', 10, 2)->after('user_id');
            $table->dateTime('claimed_at')->after('coins_claimed');
            $table->index('user_id');
            $table->index('claimed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reward_claims', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['claimed_at']);
            $table->dropColumn(['user_id', 'coins_claimed', 'claimed_at']);
        });
    }
};
