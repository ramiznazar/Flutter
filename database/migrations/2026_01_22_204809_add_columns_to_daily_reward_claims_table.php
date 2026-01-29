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
        $t = 'daily_reward_claims';
        Schema::table($t, function (Blueprint $table) use ($t) {
            if (!Schema::hasColumn($t, 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
            }
            if (!Schema::hasColumn($t, 'coins_claimed')) {
                $table->decimal('coins_claimed', 10, 2)->after('user_id');
            }
            if (!Schema::hasColumn($t, 'claimed_at')) {
                $table->dateTime('claimed_at')->after('coins_claimed');
            }
        });
        foreach (['user_id', 'claimed_at'] as $col) {
            if (!Schema::hasColumn($t, $col)) {
                continue;
            }
            $idx = $t . '_' . $col . '_index';
            $exists = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
                [$t, $idx]
            );
            if (!$exists) {
                Schema::table($t, fn (Blueprint $table) => $table->index($col));
            }
        }
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
