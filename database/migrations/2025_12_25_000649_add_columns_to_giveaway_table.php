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
        Schema::table('giveaway', function (Blueprint $table) {
            $table->decimal('reward', 10, 2)->default(0)->nullable()->after('description');
            $table->dateTime('start_date')->nullable()->after('reward');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->string('status', 50)->default('active')->nullable()->after('end_date');
            $table->text('redirect_link')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giveaway', function (Blueprint $table) {
            $table->dropColumn(['reward', 'start_date', 'end_date', 'status', 'redirect_link']);
        });
    }
};
