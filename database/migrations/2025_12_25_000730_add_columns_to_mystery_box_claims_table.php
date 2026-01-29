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
        $t = 'mystery_box_claims';
        Schema::table($t, function (Blueprint $table) use ($t) {
            if (!Schema::hasColumn($t, 'clicks')) {
                $table->integer('clicks')->default(0)->nullable()->after('box_type');
            }
            if (!Schema::hasColumn($t, 'last_clicked_at')) {
                $table->dateTime('last_clicked_at')->nullable()->after('clicks');
            }
        });
        foreach (['clicks', 'last_clicked_at'] as $col) {
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
        Schema::table('mystery_box_claims', function (Blueprint $table) {
            $table->dropIndex(['clicks', 'last_clicked_at']);
            $table->dropColumn(['clicks', 'last_clicked_at']);
        });
    }
};
