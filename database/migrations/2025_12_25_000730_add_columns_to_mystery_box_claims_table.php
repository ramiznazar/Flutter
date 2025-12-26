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
        Schema::table('mystery_box_claims', function (Blueprint $table) {
            $table->integer('clicks')->default(0)->nullable()->after('box_type');
            $table->dateTime('last_clicked_at')->nullable()->after('clicks');
            $table->index('clicks');
            $table->index('last_clicked_at');
        });
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
